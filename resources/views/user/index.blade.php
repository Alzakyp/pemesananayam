@extends('layouts.app')

@section('title', 'Data User')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Data Pengguna</h2>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <a href="{{ route('user.create') }}" class="btn btn-success">
                                        + Tambah Pengguna Baru
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
                                                    <th scope="col">Nama</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Role</th>
                                                    <th scope="col">Nomor HP</th>
                                                    <th scope="col" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($users as $item)
                                                <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                    <td>{{ $item->nama }}</td>
                                                    <td>{{ $item->email }}</td>
                                                    <td>
                                                        <span class="badge badge-pill 
                                                            @if ($item->role == 'admin') badge-primary 
                                                            @else badge-secondary 
                                                            @endif">
                                                            {{ ucfirst($item->role) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item->no_hp }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('user.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('user.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah ingin menghapus user {{ $item->nama }}?')">
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
