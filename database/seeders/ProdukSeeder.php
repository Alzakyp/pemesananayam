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
            // Menu olahan ayam
            [
                'nama_produk' => 'Ayam Goreng',
                'harga' => 25000,
                'stok' => 100,
                'satuan' => 'pcs',
                'gambar' => 'ayam_goreng.jpg'
            ],
            [
                'nama_produk' => 'Ayam Bakar',
                'harga' => 28000,
                'stok' => 80,
                'satuan' => 'pcs',
                'gambar' => 'ayam_bakar.jpg'
            ],
            [
                'nama_produk' => 'Ayam Crispy',
                'harga' => 22000,
                'stok' => 120,
                'satuan' => 'pcs',
                'gambar' => 'ayam_crispy.jpg'
            ],
            [
                'nama_produk' => 'Ayam Penyet',
                'harga' => 20000,
                'stok' => 90,
                'satuan' => 'pcs',
                'gambar' => 'ayam_penyet.jpg'
            ],
            [
                'nama_produk' => 'Ayam Geprek',
                'harga' => 18000,
                'stok' => 150,
                'satuan' => 'pcs',
                'gambar' => 'ayam_geprek.jpg'
            ],

            // Bagian-bagian ayam mentah
            [
                'nama_produk' => 'Dada Ayam',
                'harga' => 45000,
                'stok' => 85,
                'satuan' => 'Kg',
                'gambar' => 'dada_ayam.jpg'
            ],
            [
                'nama_produk' => 'Paha Ayam',
                'harga' => 42000,
                'stok' => 90,
                'satuan' => 'Kg',
                'gambar' => 'paha_ayam.jpg'
            ],
            [
                'nama_produk' => 'Sayap Ayam',
                'harga' => 38000,
                'stok' => 75,
                'satuan' => 'Kg',
                'gambar' => 'sayap_ayam.jpg'
            ],
            [
                'nama_produk' => 'Kepala Ayam',
                'harga' => 20000,
                'stok' => 60,
                'satuan' => 'Kg',
                'gambar' => 'kepala_ayam.jpg'
            ],
            [
                'nama_produk' => 'Ceker Ayam',
                'harga' => 25000,
                'stok' => 70,
                'satuan' => 'Kg',
                'gambar' => 'ceker_ayam.jpg'
            ],
            [
                'nama_produk' => 'Ayam Utuh Segar',
                'harga' => 35000,
                'stok' => 40,
                'satuan' => 'ekor',
                'gambar' => 'ayam_utuh.jpg'
            ],
        ];

        // Insert the data into the database
        foreach ($produks as $produk) {
            Produk::create($produk);
        }
    }
}
