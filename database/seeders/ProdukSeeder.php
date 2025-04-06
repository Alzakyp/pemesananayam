<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the product data to be seeded
        $produks = [
            [
                'nama_produk' => 'Ayam Goreng',
                'harga' => 25000,
                'stok' => 100,
                'gambar' => 'ayam_goreng.jpg'
            ],
            [
                'nama_produk' => 'Ayam Bakar',
                'harga' => 28000,
                'stok' => 80,
                'gambar' => 'ayam_bakar.jpg'
            ],
            [
                'nama_produk' => 'Ayam Crispy',
                'harga' => 22000,
                'stok' => 120,
                'gambar' => 'ayam_crispy.jpg'
            ],
            [
                'nama_produk' => 'Ayam Penyet',
                'harga' => 20000,
                'stok' => 90,
                'gambar' => 'ayam_penyet.jpg'
            ],
            [
                'nama_produk' => 'Ayam Geprek',
                'harga' => 18000,
                'stok' => 150,
                'gambar' => 'ayam_geprek.jpg'
            ],
        ];

        // Insert the data into the database
        foreach ($produks as $produk) {
            Produk::create($produk);
        }
    }
}
