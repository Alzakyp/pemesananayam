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
        'status',
        'tanggal_pembayaran',
    ];

    // Relasi dengan Pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}
