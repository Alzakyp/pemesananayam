@extends('layouts.app')

@section('title', 'Tambah Pesanan')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Tambah Pesanan</h2>
            <p class="dashboard-subtitle">Masukkan data pesanan baru</p>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('pesanan.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="id_pelanggan">Pelanggan</label>
                                    <select name="id_pelanggan" id="id_pelanggan" class="form-control" required>
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach ($pelanggans as $pelanggan)
                                            <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="alamat_pengiriman">Alamat Pengiriman</label>
                                    <textarea name="alamat_pengiriman" id="alamat_pengiriman" class="form-control" required></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="id_produk">Produk</label>
                                    <select name="id_produk" id="id_produk" class="form-control" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($produks as $produk)
                                            <option value="{{ $produk->id }}">{{ $produk->nama_produk }} - Rp {{ number_format($produk->harga, 2, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="total_bayar">Total Bayar</label>
                                    <input type="number" step="0.01" name="total_bayar" id="total_bayar" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="metode_pembayaran">Metode Pembayaran</label>
                                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                                        <option value="Transfer">Transfer</option>
                                        <option value="Tunai">Tunai</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="metode_pengiriman">Metode Pengiriman</label>
                                    <select name="metode_pengiriman" id="metode_pengiriman" class="form-control" required>
                                        <option value="Delivery">Delivery</option>
                                        <option value="Pick Up">Pick Up</option>
                                    </select>
                                </div>

                                <!-- <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="Menunggu Konfirmasi" selected>Menunggu Konfirmasi</option>
                                        <option value="Diproses">Diproses</option>
                                        <option value="Siap Diambil">Siap Diambil</option>
                                        <option value="Dikirim">Dikirim</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Dibatalkan">Dibatalkan</option>
                                    </select>
                                </div> -->

                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
