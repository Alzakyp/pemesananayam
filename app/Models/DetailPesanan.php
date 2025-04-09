<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model ini.
     *
     * @var string
     */
    protected $table = 'detail_pesanan';

    /**
     * Atribut yang dapat diisi.
     *
     * @var array
     */
    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'jumlah',
        'berat',
        'harga',
        'subtotal'
    ];

    /**
     * Relasi dengan pesanan.
     */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    /**
     * Relasi dengan produk.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
