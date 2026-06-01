<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
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
        $tables = Table::where('status', 'available')->get();

        return view('customer.reservation', compact('tables'));
    }

    public function publicStore(Request $req)
    {
        $data = $req->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:45',
            'email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'guests' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        // If booking is for today, also require the time to be in the future.
        if (Carbon::parse($data['reservation_date'])->isToday()) {
            $slot = Carbon::parse($data['reservation_date'].' '.$data['reservation_time']);
            if ($slot->isPast()) {
                return back()->withErrors(['reservation_time' => 'Please pick a time later than now.'])->withInput();
            }
        }

        Reservation::create($data + ['source' => 'online', 'status' => 'pending']);

        return back()->with('success','Reservation submitted! We will confirm shortly.');
    }
}
