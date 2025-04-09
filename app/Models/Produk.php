<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'produk';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama_produk',
        'harga',
        'stok',
        'satuan',
        'gambar'
    ];

    /**
     * Relasi dengan pesanan
     */
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_produk');
    }

    /**
     * Relasi dengan detail pesanan
     */
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_produk');
    }
}
