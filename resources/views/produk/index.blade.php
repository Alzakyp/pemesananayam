@extends('layouts.app')
@section('title', 'Data Produk')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Produk Kami</h2>
            <p class="dashboard-subtitle">Kelola produk dengan baik</p>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <a href="{{ route('produk.create') }}" class="btn btn-success">
                                        + Tambah Produk Baru
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">No</th>
                                                    <th scope="col">Nama</th>
                                                    <th scope="col">Harga</th>
                                                    <th scope="col">Stok</th>
                                                    <th scope="col">Satuan</th>
                                                    <th scope="col">Gambar</th>
                                                    <th scope="col" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp
                                                @foreach ($produk as $item)
                                                <tr>
                                                    <th scope="row">{{ $no++ }}</th>
                                                    <td>{{ $item->nama_produk }}</td>
                                                    <td>Rp {{ number_format($item->harga, 2, ',', '.') }}</td>
                                                    <td>
                                                        @if ($item->stok > 0)
                                                            <span class="badge badge-pill badge-success">STOK</span>
                                                        @else
                                                            <span class="badge badge-pill badge-danger">HABIS</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->satuan }}</td>
                                                    <td>
                                                        @if ($item->gambar)
                                                            <img src="{{ asset('storage/' . $item->gambar) }}" width="80" alt="Gambar Produk" style="object-fit: cover; height: 80px;">
                                                        @else
                                                            Tidak Ada Gambar
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('produk.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('produk.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah ingin menghapus {{ $item->nama_produk }}?')">
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
