<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller {
    public function index(Request $req) {
        $reservations = Reservation::with('table')
            ->when($req->status, fn($q)=>$q->where('status',$req->status))
            ->when($req->date,   fn($q)=>$q->whereDate('reservation_date',$req->date))
            ->orderBy('reservation_date')->orderBy('reservation_time')->paginate(20);

        $today            = Carbon::today();
        $todayCount       = Reservation::whereDate('reservation_date',$today)->count();
        $pendingCount     = Reservation::where('status','pending')->count();
        $confirmedCount   = Reservation::where('status','confirmed')->count();
        $cancelledCount   = Reservation::where('status','cancelled')->count();
        $todayReservations= Reservation::with('table')->whereDate('reservation_date',$today)->orderBy('reservation_time')->get();
        $tables           = Table::all();

        return view('reservations.index', compact('reservations','todayCount','pendingCount','confirmedCount','cancelledCount','todayReservations','tables'));
    }

    public function store(Request $req) {
        $req->validate(['customer_name'=>'required','phone'=>'required','reservation_date'=>'required|date','reservation_time'=>'required','guests'=>'required|integer|min:1']);
        Reservation::create($req->only(['customer_name','phone','email','reservation_date','reservation_time','guests','table_id','source','notes']));
        if ($req->table_id) Table::where('id',$req->table_id)->update(['status'=>'reserved']);
        return back()->with('success','Reservation created!');
    }

    public function updateStatus(Request $req, $id) {
        $res = Reservation::findOrFail($id);
        $res->update(['status'=>$req->status]);
        if (in_array($req->status,['cancelled','arrived']) && $res->table_id) Table::where('id',$res->table_id)->update(['status'=>'available']);
        return back()->with('success','Reservation updated!');
    }

    public function publicForm() {
        $tables = Table::where('status','available')->get();
        return view('customer.reservation', compact('tables'));
    }

    public function publicStore(Request $req) {
        $req->validate(['customer_name'=>'required','phone'=>'required','reservation_date'=>'required|date|after:yesterday','reservation_time'=>'required','guests'=>'required|integer|min:1']);
        Reservation::create($req->only(['customer_name','phone','email','reservation_date','reservation_time','guests','notes'])+['source'=>'online','status'=>'pending']);
        return back()->with('success','Reservation submitted! We will confirm shortly.');
    }
}
