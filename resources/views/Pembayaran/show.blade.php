@extends('layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pembayaran</h1>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pembayaran</h6>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr>
                            <th width="30%">ID Pembayaran</th>
                            <td>{{ $pembayaran->id }}</td>
                        </tr>
                        <tr>
                            <th>ID Pesanan</th>
                            <td>{{ $pembayaran->id_pesanan }}</td>
                        </tr>
                        <tr>
                            <th>Metode</th>
                            <td>
                                {{ $pembayaran->metode }}
                                @if($pembayaran->midtrans_payment_type)
                                    <small class="d-block text-muted">{{ $pembayaran->midtrans_payment_type }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($pembayaran->status_pemrosesan == 'Menunggu Pembayaran')
                                    <span class="badge badge-warning">Menunggu Pembayaran</span>
                                @elseif($pembayaran->status_pemrosesan == 'Diproses')
                                    <span class="badge badge-success">Diproses</span>
                                @elseif($pembayaran->status_pemrosesan == 'Selesai')
                                    <span class="badge badge-primary">Selesai</span>
                                @elseif($pembayaran->status_pemrosesan == 'Ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>{{ $pembayaran->tanggal_pembayaran ? $pembayaran->tanggal_pembayaran->format('d/m/Y H:i') : 'Belum Bayar' }}</td>
                        </tr>
                        @if($pembayaran->midtrans_transaction_id)
                        <tr>
                            <th>Midtrans ID</th>
                            <td>{{ $pembayaran->midtrans_transaction_id }}</td>
                        </tr>
                        @endif
                        @if($pembayaran->midtrans_status)
                        <tr>
                            <th>Status Midtrans</th>
                            <td>{{ $pembayaran->midtrans_status }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>{{ $pembayaran->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Update Terakhir</th>
                            <td>{{ $pembayaran->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Status pembayaran diperbarui otomatis oleh Midtrans melalui webhook.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pesanan</h6>
                </div>
                <div class="card-body">
                    @if($pembayaran->pesanan)
                    <table class="table table-striped">
                        <tr>
                            <th width="30%">ID Pesanan</th>
                            <td>#{{ $pembayaran->pesanan->id }}</td>
                        </tr>
                        <tr>
                            <th>Pelanggan</th>
                            <td>
                                @if($pembayaran->pesanan->pelanggan)
                                    {{ $pembayaran->pesanan->pelanggan->nama }}
                                @elseif($pembayaran->pesanan->nama)
                                    {{ $pembayaran->pesanan->nama }} <span class="badge badge-info">Guest</span>
                                @else
                                    <span class="text-muted">Data tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total Bayar</th>
                            <td>Rp {{ number_format($pembayaran->pesanan->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Status Pesanan</th>
                            <td>
                                <span class="badge badge-{{
                                    $pembayaran->pesanan->status == 'Menunggu Konfirmasi' ? 'warning' :
                                    ($pembayaran->pesanan->status == 'Diproses' ? 'info' :
                                    ($pembayaran->pesanan->status == 'Dikirim' ? 'primary' :
                                    ($pembayaran->pesanan->status == 'Selesai' ? 'success' :
                                    ($pembayaran->pesanan->status == 'Ditolak' ? 'danger' : 'secondary'))))
                                }}">
                                    {{ $pembayaran->pesanan->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pesanan</th>
                            <td>{{ $pembayaran->pesanan->tanggal_pemesanan->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $pembayaran->pesanan->alamat_pengiriman }}</td>
                        </tr>
                    </table>
                    @else
                    <div class="alert alert-warning">
                        Data pesanan tidak ditemukan atau mungkin telah dihapus.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
