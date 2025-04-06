@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="section-content section-dashboard-home" data-aos="fade-up">
    <div class="container-fluid">
        <div class="dashboard-heading">
            <h2 class="dashboard-title">Tambah Pengguna</h2>
            <p class="dashboard-subtitle">Masukkan data pengguna baru</p>
        </div>
        <div class="dashboard-content">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('user.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap</label>
                                    <input type="text" name="nama" id="nama" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="admin">Admin</option>
                                        <option value="pelanggan">Pelanggan</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="no_hp">Nomor HP</label>
                                    <input type="text" name="no_hp" id="no_hp" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('user.index') }}" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
