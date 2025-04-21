@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="m-0 font-weight-bold">Detail Pesanan #{{ $pesanan->id }}</h4>
                        <div>
                            <a href="{{ route('pesanan.index') }}" class="btn btn-light">Kembali</a>
                            <a href="{{ route('pesanan.edit', $pesanan->id) }}" class="btn btn-warning">Edit</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Informasi Pesanan -->
                            <div class="col-md-6">
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-primary py-3">
                                        <h6 class="m-0 font-weight-bold text-white">
                                            <i class="fas fa-info-circle mr-2"></i>Informasi Pesanan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">ID Pesanan</th>
                                                <td>{{ $pesanan->id }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Pemesanan</th>
                                                <td>{{ $pesanan->tanggal_pemesanan->format('d M Y, H:i') }} WIB</td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Pengiriman</th>
                                                <td>
                                                    @if($pesanan->tanggal_pengiriman)
                                                        {{ $pesanan->tanggal_pengiriman->format('d M Y') }}
                                                        @if($pesanan->tanggal_pengiriman->isAfter(\Carbon\Carbon::today()))
                                                            <span class="badge badge-info">Pengiriman di masa depan</span>
                                                        @endif
                                                    @else
                                                        {{ $pesanan->tanggal_pemesanan->format('d M Y') }} <small class="text-muted">(Sama dengan pemesanan)</small>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    @if($pesanan->status == 'Mempersiapkan')
                                                        <span class="badge badge-info">Mempersiapkan</span>
                                                    @elseif($pesanan->status == 'Proses pengantaran')
                                                        <span class="badge badge-primary">Proses pengantaran</span>
                                                    @elseif($pesanan->status == 'Selesai')
                                                        <span class="badge badge-success">Selesai</span>
                                                    @elseif($pesanan->status == 'Dibatalkan')
                                                        <span class="badge badge-danger">Dibatalkan</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total Bayar</th>
                                                <td>Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Metode Pembayaran</th>
                                                <td>{{ $pesanan->metode_pembayaran }}</td>
                                            </tr>
                                            <tr>
                                                <th>Metode Pengiriman</th>
                                                <td>{{ $pesanan->metode_pengiriman }}</td>
                                            </tr>
                                            @if(isset($pesanan->stok_dikurangi))
                                            <tr>
                                                <th>Status Stok</th>
                                                <td>
                                                    @if($pesanan->stok_dikurangi)
                                                        <span class="badge badge-success">Stok sudah dikurangi</span>
                                                    @else
                                                        <span class="badge badge-warning">Stok belum dikurangi</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Pelanggan & Status Update -->
                            <div class="col-md-6">
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-header bg-gradient-success py-3">
                                        <h6 class="m-0 font-weight-bold text-white">
                                            <i class="fas fa-user mr-2"></i>Informasi Pelanggan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="40%">Nama</th>
                                                <td>
                                                    @if($pesanan->pelanggan)
                                                        {{ $pesanan->pelanggan->nama }}
                                                    @else
                                                        {{ $pesanan->nama ?: 'Guest' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>No. HP</th>
                                                <td>
                                                    @if($pesanan->pelanggan)
                                                        <a href="tel:{{ $pesanan->pelanggan->no_hp }}">{{ $pesanan->pelanggan->no_hp }}</a>
                                                    @else
                                                        {{ $pesanan->no_hp ?: '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Alamat Pengiriman</th>
                                                <td>{{ $pesanan->alamat_pengiriman }}</td>
                                            </tr>
                                            @if($pesanan->lokasi_maps)
                                                <tr>
                                                    <th>Lokasi Maps</th>
                                                    <td>
                                                        <a href="https://maps.google.com/?q={{ $pesanan->lokasi_maps }}" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-map-marker-alt"></i> Lihat Lokasi di Google Maps
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>

                                <!-- Update Status Section -->
                                <div class="card shadow-sm">
                                    <div class="card-header bg-gradient-warning py-3">
                                        <h6 class="m-0 font-weight-bold text-white">
                                            <i class="fas fa-tasks mr-2"></i>Update Status Pesanan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('pesanan.updateStatus', $pesanan->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label for="status"><strong>Status Pesanan</strong></label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="Mempersiapkan" {{ $pesanan->status == 'Mempersiapkan' ? 'selected' : '' }}>Mempersiapkan</option>
                                                    <option value="Proses pengantaran" {{ $pesanan->status == 'Proses pengantaran' ? 'selected' : '' }}>Proses pengantaran</option>
                                                    <option value="Selesai" {{ $pesanan->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                    <option value="Dibatalkan" {{ $pesanan->status == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-save mr-2"></i>Update Status
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Produk -->
                        <div class="card mt-4 shadow-sm">
                            <div class="card-header bg-gradient-info py-3">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-box mr-2"></i>Detail Produk
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Produk</th>
                                                    <th>Jumlah</th>
                                                    <th>Harga</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pesanan->detailPesanan as $index => $detail)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $detail->produk->nama_produk }}</td>
                                                        <td>{{ $detail->jumlah }} {{ $detail->produk->satuan }}</td>
                                                        <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="4" class="text-right">Total</th>
                                                    <th>Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @elseif($pesanan->produk)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th>Jumlah</th>
                                                    <th>Harga</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{ $pesanan->produk->nama_produk }}</td>
                                                    <td>1 {{ $pesanan->produk->satuan }}</td>
                                                    <td>Rp {{ number_format($pesanan->produk->harga, 0, ',', '.') }}</td>
                                                    <td>Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>Tidak ada detail produk.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pembayaran -->
                        @if($pesanan->pembayaran)
                            <div class="card mt-4 shadow-sm">
                                <div class="card-header bg-gradient-secondary py-3">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-credit-card mr-2"></i>Informasi Pembayaran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="20%">ID Pembayaran</th>
                                            <td>{{ $pesanan->pembayaran->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Metode</th>
                                            <td>
                                                {{ $pesanan->pembayaran->metode }}
                                                @if($pesanan->pembayaran->midtrans_payment_type)
                                                    <span class="badge badge-info">
                                                        {{ ucwords(str_replace('_', ' ', $pesanan->pembayaran->midtrans_payment_type)) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($pesanan->pembayaran->status_pemrosesan == 'Menunggu Pembayaran')
                                                    <span class="badge badge-warning">Menunggu Pembayaran</span>
                                                @elseif($pesanan->pembayaran->status_pemrosesan == 'Diproses')
                                                    <span class="badge badge-info">Diproses</span>
                                                @elseif($pesanan->pembayaran->status_pemrosesan == 'Selesai')
                                                    <span class="badge badge-success">Selesai</span>
                                                @elseif($pesanan->pembayaran->status_pemrosesan == 'Ditolak')
                                                    <span class="badge badge-danger">Ditolak</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Pembayaran</th>
                                            <td>
                                                @if($pesanan->pembayaran->tanggal_pembayaran)
                                                    {{ $pesanan->pembayaran->tanggal_pembayaran->format('d M Y, H:i') }} WIB
                                                @else
                                                    <span class="text-warning">Belum dibayar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
