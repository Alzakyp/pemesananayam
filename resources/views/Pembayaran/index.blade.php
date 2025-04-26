@extends('layouts.app')

@section('title', 'Monitoring Pembayaran')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Monitoring Pembayaran</h1>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Status Pembayaran (Via Webhook)</h6>
                        <div class="d-flex">
                            <a href="{{ route('pembayaran.fixDuplicates') }}" class="btn btn-sm btn-warning mr-2">
                                <i class="fas fa-broom mr-1"></i> Perbaiki Duplikasi
                            </a>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Info:</div>
                                    <a class="dropdown-item" href="#" data-toggle="modal"
                                        data-target="#webhookInfoModal">
                                        <i class="fas fa-info-circle fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Tentang Webhook
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session('info'))
                            <div class="alert alert-info">
                                {{ session('info') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered" id="table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Metode</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pembayaran as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>
                                                @if ($item->pesanan)
                                                    #{{ $item->pesanan->id }} - Rp
                                                    {{ number_format($item->pesanan->total_bayar, 0, ',', '.') }}
                                                @else
                                                    <span class="text-muted">Data tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->pesanan && $item->pesanan->pelanggan)
                                                    {{ $item->pesanan->pelanggan->nama }}
                                                @elseif($item->pesanan && $item->pesanan->nama)
                                                    {{ $item->pesanan->nama }} <span class="badge badge-info">Guest</span>
                                                @else
                                                    <span class="text-muted">Data tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (strtolower($item->metode) == 'midtrans')
                                                    QRIS
                                                @else
                                                    {{ $item->metode }}
                                                @endif

                                                @if ($item->midtrans_payment_type)
                                                    <small
                                                        class="d-block text-muted">{{ $item->midtrans_payment_type }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->status_pemrosesan == 'Menunggu Pembayaran')
                                                    <span class="badge badge-warning">Menunggu Pembayaran</span>
                                                @elseif($item->status_pemrosesan == 'Diproses')
                                                    <span class="badge badge-success">Diproses</span>
                                                @elseif($item->status_pemrosesan == 'Selesai')
                                                    <span class="badge badge-primary">Selesai</span>
                                                @elseif($item->status_pemrosesan == 'Gagal')
                                                    <span class="badge badge-danger">Gagal</span>
                                                @else
                                                    <span
                                                        class="badge badge-secondary">{{ $item->status_pemrosesan }}</span>
                                                @endif

                                                @if (
                                                    $item->pesanan &&
                                                        ($item->pesanan->status == 'Proses pengantaran' || $item->pesanan->status == 'Siap Diambil') &&
                                                        $item->status_pemrosesan == 'Menunggu Pembayaran')
                                                    <span class="badge badge-danger">Status Tidak Sinkron</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->tanggal_pembayaran)
                                                    @if (is_string($item->tanggal_pembayaran))
                                                        {{ \Carbon\Carbon::parse($item->tanggal_pembayaran)->format('d/m/Y H:i') }}
                                                    @else
                                                        {{ $item->tanggal_pembayaran->format('d/m/Y H:i') }}
                                                    @endif
                                                @else
                                                    <span class="text-muted">Belum Bayar</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('pembayaran.show', $item->id) }}"
                                                    class="btn btn-info btn-sm" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (
                                                    $item->pesanan &&
                                                        ($item->pesanan->status == 'Proses pengantaran' || $item->pesanan->status == 'Siap Diambil') &&
                                                        $item->status_pemrosesan == 'Menunggu Pembayaran')
                                                    <a href="{{ route('pembayaran.sync', $item->id) }}"
                                                        class="btn btn-warning btn-sm" title="Sinkronisasi Status">
                                                        <i class="fas fa-sync"></i>
                                                    </a>
                                                @endif
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

    <!-- Webhook Info Modal -->
    <div class="modal fade" id="webhookInfoModal" tabindex="-1" role="dialog" aria-labelledby="webhookInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="webhookInfoModalLabel">Informasi QRIS Webhook</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Sistem ini menggunakan <strong>QRIS Webhook</strong> untuk memproses status pembayaran otomatis:</p>
                    <ul>
                        <li><strong>settlement</strong> - Pembayaran berhasil, status otomatis berubah menjadi "Diproses"
                        </li>
                        <li><strong>pending</strong> - Pembayaran menunggu konfirmasi</li>
                        <li><strong>deny/cancel/expire</strong> - Pembayaran ditolak/dibatalkan/kadaluarsa</li>
                    </ul>
                    <p class="bg-light p-2"><strong>URL Webhook:</strong> {{ url('/api/midtrans/notification') }}</p>
                    <p class="text-warning">Perubahan status manual tidak disarankan karena akan dioverride oleh webhook.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#table').DataTable({
                    "order": [
                        [0, "desc"]
                    ],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });
            });
        </script>
    @endpush
@endsection
