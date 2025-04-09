<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini.
     *
     * @var string
     */
    protected $table = 'pesanan';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'id_pelanggan',
        'nama',
        'no_hp',
        'alamat_pengiriman',
        'id_produk',
        'total_bayar',
        'metode_pembayaran',
        'metode_pengiriman',
        'lokasi_maps',
        'status',
        'tanggal_pemesanan'
    ];

    /**
     * Relasi dengan pelanggan.
     */
    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'id_pelanggan');
    }

    /**
     * Relasi dengan produk (untuk sistem lama).
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    /**
     * Relasi dengan detail pesanan.
     */
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan');
    }

    /**
     * Relasi dengan pembayaran.
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pesanan');
    }
}
