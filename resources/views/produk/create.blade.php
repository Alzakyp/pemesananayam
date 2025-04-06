@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Tambah Produk</h2>
            <p class="dashboard-subtitle">Tambahkan produk baru ke dalam daftar</p>
        </div>
        <div class="dashboard-content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="nama_produk">Nama Produk</label>
                                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga">Harga</label>
                                    <input type="number" class="form-control" id="harga" name="harga" required min="1">
                                </div>
                                <div class="form-group">
                                    <label for="stok">Stok</label>
                                    <input type="number" class="form-control" id="stok" name="stok" required min="0">
                                </div>
                                <div class="form-group">
                                    <label for="satuan">Satuan</label>
                                    <input type="text" class="form-control" id="satuan" name="satuan" required>
                                </div>
                                <div class="form-group">
                                    <label for="gambar">Gambar</label>
                                    <input type="file" class="form-control-file" id="gambar" name="gambar">
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Produk</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
