@extends('layouts.app')

@section('title', 'Data Pesanan')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Pesanan Kami</h2>
            <p class="dashboard-subtitle">Kelola pesanan dengan baik</p>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <!-- <a href="{{ route('pesanan.create') }}" class="btn btn-success">
                                        + Tambah Pesanan Baru
                                    </a> -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">ID</th>
                                                    <th scope="col">Pelanggan</th>
                                                    <th scope="col">Alamat Pengiriman</th>
                                                    <th scope="col">Produk</th>
                                                    <th scope="col">Total Bayar</th>
                                                    <th scope="col">Metode Pembayaran</th>
                                                    <th scope="col">Metode Pengiriman</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pesanans as $item)
                                                <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>{{ $item->pelanggan->nama }}</td>
                                                    <td>{{ $item->alamat_pengiriman }}</td>
                                                    <td>{{ $item->produk->nama_produk }}</td>
                                                    <td>Rp {{ number_format($item->total_bayar, 2, ',', '.') }}</td>
                                                    <td>{{ $item->metode_pembayaran }}</td>
                                                    <td>{{ $item->metode_pengiriman }}</td>
                                                    <td>
                                                        <span class="badge badge-pill 
                                                            @if ($item->status == 'Selesai') badge-success 
                                                            @elseif ($item->status == 'Dibatalkan') badge-danger 
                                                            @else badge-warning 
                                                            @endif">
                                                            {{ $item->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('pesanan.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('pesanan.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah ingin menghapus pesanan {{ $item->id }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
