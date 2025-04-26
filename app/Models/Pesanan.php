<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'id_pelanggan',
        'nama',
        'no_hp',
        'alamat_pengiriman',
        'id_produk',
        'jumlah',
        'berat',
        'total_bayar',
        'metode_pembayaran',
        'metode_pengiriman',
        'lokasi_maps',
        'status',
        'stok_dikurangi',
        'tanggal_pemesanan',
        'tanggal_pengiriman',
        'tanggal_pembayaran',
        'payment_details',
        'midtrans_order_id',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'tanggal_pemesanan' => 'datetime',
        'tanggal_pengiriman' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
    ];

    // Relasi ke model Produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    // Relasi ke model User (Pelanggan)
    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'id_pelanggan');
    }

    // Relasi ke model DetailPesanan
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan');
    }

    // Relasi ke model Pembayaran
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan');
    }
}
