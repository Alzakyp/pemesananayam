@extends('layouts.app')

@section('title', 'Data Pembayaran')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Pembayaran</h2>
            <p class="dashboard-subtitle">Kelola pembayaran dengan baik</p>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <a href="" class="btn btn-success">
                                        + Tambah Pembayaran Baru
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">ID</th>
                                                    <th scope="col">Nama Pelanggan</th>
                                                    <th scope="col">Total Bayar</th>
                                                    <th scope="col">Metode Pembayaran</th>
                                                    <th scope="col">Tanggal Pembayaran</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pembayaran as $item)
                                                <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>                                                    <td>{{ $item->pelanggan->name }}</td>
                                                    <td>Rp {{ number_format($item->total_bayar, 2, ',', '.') }}</td>
                                                    <td>{{ $item->metode_pembayaran }}</td>
                                                    <!-- <td>
                                                        @if($item->bukti_transfer)
                                                            <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank">Lihat</a>
                                                        @else
                                                            <span class="text-danger">Belum Diupload</span>
                                                        @endif
                                                    </td> -->
                                                    <td>{{ date('d-m-Y', strtotime($item->tanggal_pembayaran)) }}</td>
                                                    <td>
                                                        <span class="badge badge-pill 
                                                            @if ($item->status == 'Terverifikasi') badge-success 
                                                            @elseif ($item->status == 'Ditolak') badge-danger 
                                                            @else badge-warning 
                                                            @endif">
                                                            {{ $item->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('pembayaran.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('pembayaran.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah ingin menghapus pembayaran {{ $item->id }}?')">
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
