<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan'; // Nama tabel

    protected $fillable = [
        'id_pelanggan',
        'alamat_pengiriman',
        'id_produk',
        'total_bayar',
        'metode_pembayaran',
        'metode_pengiriman',
        'status',
        'tanggal_pemesanan',
    ];

    // Relasi dengan model User (Pelanggan)
    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'id_pelanggan');
    }

    // Relasi dengan model Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
