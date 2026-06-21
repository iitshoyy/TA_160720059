<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    /** Fixed hourly booking slots, 10:00–20:00. Single source of truth for view + validation. */
    public const SLOTS = ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];

    public function index(Request $req)
    {
        $reservations = Reservation::with('table')
            ->when($req->status, fn ($q) => $q->where('status', $req->status))
            ->when($req->date, fn ($q) => $q->whereDate('reservation_date', $req->date))
            ->orderBy('reservation_date')->orderBy('reservation_time')->paginate(20);

        $today = Carbon::today();
        $todayCount = Reservation::whereDate('reservation_date', $today)->count();
        $pendingCount = Reservation::where('status', 'pending')->count();
        $confirmedCount = Reservation::where('status', 'confirmed')->count();
        $cancelledCount = Reservation::where('status', 'cancelled')->count();
        $todayReservations = Reservation::with('table')->whereDate('reservation_date', $today)->orderBy('reservation_time')->get();
        $upcomingReservations = Reservation::with('table')
            ->whereBetween('reservation_date', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('reservation_date')->orderBy('reservation_time')
            ->get();
        $tables = Table::all();

        return view('reservations.index', compact(
            'reservations', 'todayCount', 'pendingCount', 'confirmedCount', 'cancelledCount',
            'todayReservations', 'upcomingReservations', 'tables'
        ));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'guests' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:tables,id',
            'source' => 'nullable|in:online,offline,whatsapp,phone',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data) {
            Reservation::create($data);
            if (! empty($data['table_id'])) {
                Table::where('id', $data['table_id'])->update(['status' => 'reserved']);
            }
        });

        return back()->with('success', 'Reservation created!');
    }

    public function update(Request $req, $id)
    {
        $data = $req->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'guests' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:tables,id',
            'notes' => 'nullable|string|max:500',
        ]);
        Reservation::findOrFail($id)->update($data);

        return back()->with('success', 'Reservation updated!');
    }

    public function updateStatus(Request $req, $id)
    {
        $data = $req->validate([
            'status' => 'required|in:pending,confirmed,arrived,cancelled',
        ]);
        DB::transaction(function () use ($data, $id) {
            $res = Reservation::lockForUpdate()->findOrFail($id);
            $res->update(['status' => $data['status']]);

            // Cancelled releases the table; 'arrived' should keep it OCCUPIED (guest is sitting there).
            if ($res->table_id) {
                if ($data['status'] === 'cancelled') {
                    Table::where('id', $res->table_id)->update(['status' => 'available']);
                } elseif ($data['status'] === 'arrived') {
                    Table::where('id', $res->table_id)->update(['status' => 'occupied']);
                }
            }
        });

        return back()->with('success', 'Reservation updated!');
    }

    public function destroy($id)
    {
        $res = Reservation::findOrFail($id);
        DB::transaction(function () use ($res) {
            if ($res->table_id && $res->status !== 'arrived') {
                Table::where('id', $res->table_id)->update(['status' => 'available']);
            }
            $res->delete();
        });

        return back()->with('success', 'Reservation removed!');
    }

    public function publicForm()
    {
        // Pass ALL tables (not just status=available) — availability is per date+slot, computed client-side.
        $tables = Table::orderBy('name')->get(['id', 'name', 'capacity', 'pos_x', 'pos_y']);
        $slots = self::SLOTS;

        return view('customer.reservation', compact('tables', 'slots'));
    }

    public function availability(Request $req)
    {
        $data = $req->validate([
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|in:'.implode(',', self::SLOTS),
            'guests' => 'required|integer|min:1',
        ]);

        // A slot already in the past (today only) is never available.
        if (Carbon::parse($data['date'])->isToday()
            && Carbon::parse($data['date'].' '.$data['time'])->isPast()) {
            return response()->json(['available' => []]);
        }

        $taken = Reservation::whereDate('reservation_date', $data['date'])
            ->whereTime('reservation_time', Carbon::parse($data['time'])->format('H:i:s'))
            ->where('status', '!=', 'cancelled')
            ->pluck('table_id')
            ->filter()
            ->all();

        $available = Table::where('capacity', '>=', $data['guests'])
            ->whereNotIn('id', $taken)
            ->pluck('id')
            ->all();

        return response()->json(['available' => $available]);
    }

    public function publicStore(Request $req)
    {
        $data = $req->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|in:'.implode(',', self::SLOTS),
            'guests' => 'required|integer|min:1',
            'table_id' => 'required|exists:tables,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // If booking is for today, the time must still be in the future.
        if (Carbon::parse($data['reservation_date'])->isToday()) {
            $slot = Carbon::parse($data['reservation_date'].' '.$data['reservation_time']);
            if ($slot->isPast()) {
                return back()->withErrors(['reservation_time' => 'Please pick a time later than now.'])->withInput();
            }
        }

        // Lock the table, re-check capacity and slot conflict, then create — prevents double-booking races.
        DB::transaction(function () use ($data) {
            $table = Table::lockForUpdate()->findOrFail($data['table_id']);

            if ($table->capacity < $data['guests']) {
                throw ValidationException::withMessages([
                    'table_id' => 'That table is too small for your party.',
                ]);
            }

            $conflict = Reservation::whereDate('reservation_date', $data['reservation_date'])
                ->whereTime('reservation_time', Carbon::parse($data['reservation_time'])->format('H:i:s'))
                ->where('table_id', $data['table_id'])
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'table_id' => 'Sorry, that table was just booked for this time. Please pick another.',
                ]);
            }

            Reservation::create($data + ['source' => 'online', 'status' => 'pending']);
        });

        return back()->with('success', 'Reservation submitted! We will confirm shortly.');
    }
}
