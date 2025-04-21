@extends('layouts.app')

@section('title', 'Edit Status Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Status Pembayaran #{{ $pembayaran->id }}</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informasi Pembayaran (Readonly) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ID Pesanan</label>
                                    <input type="text" class="form-control" value="{{ $pembayaran->pesanan ? $pembayaran->pesanan->id : 'N/A' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Metode Pembayaran</label>
                                    <input type="text" class="form-control" value="{{ $pembayaran->metode }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pelanggan</label>
                                    <input type="text" class="form-control" value="{{ $pembayaran->pesanan && $pembayaran->pesanan->pelanggan ? $pembayaran->pesanan->pelanggan->nama : ($pembayaran->pesanan && $pembayaran->pesanan->nama ? $pembayaran->pesanan->nama . ' (Guest)' : 'N/A') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Bayar</label>
                                    <input type="text" class="form-control" value="{{ $pembayaran->pesanan ? 'Rp ' . number_format($pembayaran->pesanan->total_bayar, 0, ',', '.') : 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Status Pembayaran (Editable) -->
                        <div class="form-group mb-4">
                            <label for="status_pemrosesan">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="status_pemrosesan" id="status_pemrosesan" class="form-control @error('status_pemrosesan') is-invalid @enderror" required>
                                <option value="Menunggu Pembayaran" {{ $pembayaran->status_pemrosesan == 'Menunggu Pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                                <option value="Diproses" {{ $pembayaran->status_pemrosesan == 'Diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="Selesai" {{ $pembayaran->status_pemrosesan == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Ditolak" {{ $pembayaran->status_pemrosesan == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Perhatian:</strong> Mengubah status pembayaran akan otomatis mengubah status pesanan terkait.
                            </small>
                            @error('status_pemrosesan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bukti Transfer (Jika Ada) -->
                        @if($pembayaran->bukti_transfer)
                        <div class="form-group mb-4">
                            <label>Bukti Transfer</label>
                            <div>
                                <img src="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" alt="Bukti Transfer" class="img-thumbnail" style="max-height: 200px">
                            </div>
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
