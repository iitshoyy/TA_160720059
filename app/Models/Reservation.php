<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model {
    protected $fillable = [
        'customer_name','phone','email','reservation_date','reservation_time',
        'guests','table_id','status','source','notes',
    ];
    public function table() { return $this->belongsTo(Table::class); }

    /** Build a click-to-chat WhatsApp link with a pre-filled Indonesian confirmation message. */
    public function whatsappUrl(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        $date = \Carbon\Carbon::parse($this->reservation_date)->format('d M Y');
        $time = \Carbon\Carbon::parse($this->reservation_time)->format('H:i');
        $table = $this->table->name ?? 'akan ditentukan';

        $message = "Halo {$this->customer_name}, reservasi Anda di RestoPOS untuk {$date} "
            ."pukul {$time} ({$this->guests} orang, meja {$table}) telah kami terima. "
            .'Mohon konfirmasi kehadiran Anda. Terima kasih!';

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
