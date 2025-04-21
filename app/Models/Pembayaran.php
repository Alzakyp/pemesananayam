<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_pesanan',
        'metode',
        'bukti_transfer',
        'midtrans_transaction_id',
        'midtrans_status',
        'midtrans_payment_type',
        'midtrans_payment_url',
        'status_pemrosesan',
        'tanggal_pembayaran',
    ];

    protected $dates = [
        'tanggal_pembayaran',
        'created_at',
        'updated_at'
    ];

    /**
     * Relasi dengan Pesanan
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}
