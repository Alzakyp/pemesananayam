@extends('layouts.app')

@section('title', 'Daftar Pesanan')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Daftar Pesanan</h1>
            <a href="{{ route('pesanan.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Pesanan
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tabel Pesanan</h6>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Metode Pembayaran</th>
                                <th>Pengiriman</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pesanans as $index => $pesanan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>Pesan: {{ $pesanan->tanggal_pemesanan->format('d/m/Y') }}</div>
                                        @if ($pesanan->tanggal_pengiriman)
                                            <div class="text-primary">Kirim:
                                                {{ $pesanan->tanggal_pengiriman->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($pesanan->pelanggan)
                                            {{ $pesanan->pelanggan->nama }}
                                        @else
                                            {{ $pesanan->nama ?: 'Guest' }}
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</td>
                                    <td>{{ $pesanan->metode_pembayaran }}</td>
                                    <td>{{ $pesanan->metode_pengiriman }}</td>
                                    <td>
                                        @if ($pesanan->status == 'Mempersiapkan')
                                            <span class="badge badge-info">Mempersiapkan</span>
                                        @elseif($pesanan->status == 'Proses pengantaran')
                                            <span class="badge badge-primary">Proses pengantaran</span>
                                        @elseif($pesanan->status == 'Siap Diambil')
                                            <span class="badge badge-warning">Siap Diambil</span>
                                        @elseif($pesanan->status == 'Selesai')
                                            <span class="badge badge-success">Selesai</span>
                                        @elseif($pesanan->status == 'Dibatalkan')
                                            <span class="badge badge-danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pesanan.show', $pesanan->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pesanan.edit', $pesanan->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('pesanan.destroy', $pesanan->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
@endsection
