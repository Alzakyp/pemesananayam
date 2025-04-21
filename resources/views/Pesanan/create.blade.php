@extends('layouts.app')

@section('title', 'Tambah Pesanan')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Pesanan</h4>
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

                        <form action="{{ route('pesanan.store') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="id_pelanggan">Pelanggan <span class="text-danger">*</span></label>
                                        <select class="form-control @error('id_pelanggan') is-invalid @enderror" id="id_pelanggan" name="id_pelanggan" required>
                                            <option value="">-- Pilih Pelanggan --</option>
                                            @foreach($pelanggans as $pelanggan)
                                                <option value="{{ $pelanggan->id }}" {{ old('id_pelanggan') == $pelanggan->id ? 'selected' : '' }}>
                                                    {{ $pelanggan->nama }} ({{ $pelanggan->no_hp }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_pelanggan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="alamat_pengiriman">Alamat Pengiriman <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('alamat_pengiriman') is-invalid @enderror" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required>{{ old('alamat_pengiriman') }}</textarea>
                                        @error('alamat_pengiriman')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="id_produk">Produk <span class="text-danger">*</span></label>
                                        <select class="form-control @error('id_produk') is-invalid @enderror" id="id_produk" name="id_produk" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach($produks as $produk)
                                                <option value="{{ $produk->id }}" data-harga="{{ $produk->harga }}" {{ old('id_produk') == $produk->id ? 'selected' : '' }}>
                                                    {{ $produk->nama_produk }} - Rp {{ number_format($produk->harga, 0, ',', '.') }} (Stok: {{ $produk->stok }} {{ $produk->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="total_bayar">Total Bayar (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('total_bayar') is-invalid @enderror" id="total_bayar" name="total_bayar" value="{{ old('total_bayar') }}" min="0" required>
                                        @error('total_bayar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="metode_pembayaran">Metode Pembayaran <span class="text-danger">*</span></label>
                                        <select class="form-control @error('metode_pembayaran') is-invalid @enderror" id="metode_pembayaran" name="metode_pembayaran" required>
                                            <option value="">-- Pilih Metode Pembayaran --</option>
                                            <option value="Midtrans" {{ old('metode_pembayaran') == 'Midtrans' ? 'selected' : '' }}>Midtrans (Online)</option>
                                            <option value="Tunai" {{ old('metode_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                        </select>
                                        @error('metode_pembayaran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="metode_pengiriman">Metode Pengiriman <span class="text-danger">*</span></label>
                                        <select class="form-control @error('metode_pengiriman') is-invalid @enderror" id="metode_pengiriman" name="metode_pengiriman" required>
                                            <option value="">-- Pilih Metode Pengiriman --</option>
                                            <option value="Delivery" {{ old('metode_pengiriman') == 'Delivery' ? 'selected' : '' }}>Delivery</option>
                                            <option value="Pick Up" {{ old('metode_pengiriman') == 'Pick Up' ? 'selected' : '' }}>Pick Up</option>
                                        </select>
                                        @error('metode_pengiriman')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Tambahkan Field Tanggal Pengiriman -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="tanggal_pengiriman">Tanggal Pengiriman <span class="text-info">(Opsional)</span></label>
                                        <input type="date" class="form-control @error('tanggal_pengiriman') is-invalid @enderror" id="tanggal_pengiriman" name="tanggal_pengiriman" value="{{ old('tanggal_pengiriman') }}" min="{{ date('Y-m-d') }}">
                                        <small class="form-text text-muted">Kosongkan untuk pengiriman hari ini. Jika diisi tanggal di masa depan, pembayaran tetap dilakukan sekarang.</small>
                                        @error('tanggal_pengiriman')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 delivery-only" style="display: none;">
                                        <label for="lokasi_maps">Lokasi Maps <span class="text-info">(Opsional)</span></label>
                                        <input type="text" class="form-control @error('lokasi_maps') is-invalid @enderror" id="lokasi_maps" name="lokasi_maps" value="{{ old('lokasi_maps') }}" placeholder="contoh: -6.123456,106.123456">
                                        <small class="form-text text-muted">Format: latitude,longitude</small>
                                        @error('lokasi_maps')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
                                <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-fill alamat pengiriman from pelanggan
        const pelangganSelect = document.getElementById('id_pelanggan');
        const alamatField = document.getElementById('alamat_pengiriman');
        const lokasiMapsField = document.getElementById('lokasi_maps');

        // Show/hide lokasi_maps field based on metode_pengiriman
        const metodeSelect = document.getElementById('metode_pengiriman');
        const deliveryFields = document.querySelectorAll('.delivery-only');

        function toggleDeliveryFields() {
            deliveryFields.forEach(field => {
                field.style.display = metodeSelect.value === 'Delivery' ? 'block' : 'none';
            });
        }

        metodeSelect.addEventListener('change', toggleDeliveryFields);
        toggleDeliveryFields(); // Call on page load

        // Auto-calculate total when product is selected
        const produkSelect = document.getElementById('id_produk');
        const totalField = document.getElementById('total_bayar');

        produkSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const harga = selectedOption.dataset.harga;
                totalField.value = harga;
            } else {
                totalField.value = '';
            }
        });
    });
</script>
@endpush
