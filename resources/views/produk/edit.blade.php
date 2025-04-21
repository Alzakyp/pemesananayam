@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit Produk</h4>
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

                        <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('nama_produk') is-invalid @enderror" id="nama_produk"
                                            name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}"
                                            required>
                                        @error('nama_produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="harga">Harga (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('harga') is-invalid @enderror"
                                            id="harga" name="harga" value="{{ old('harga', $produk->harga) }}"
                                            min="0" step="100" required>
                                        @error('harga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="stok">Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('stok') is-invalid @enderror"
                                            id="stok" name="stok" value="{{ old('stok', $produk->stok) }}"
                                            min="0" required>
                                        @error('stok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="satuan">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-control @error('satuan') is-invalid @enderror" id="satuan"
                                            name="satuan" required>
                                            <option value="">-- Pilih Satuan --</option>
                                            <option value="pcs"
                                                {{ old('satuan', $produk->satuan) == 'pcs' ? 'selected' : '' }}>pcs (Potong)
                                            </option>
                                            <option value="Kg"
                                                {{ old('satuan', $produk->satuan) == 'Kg' ? 'selected' : '' }}>Kg (Kilogram)
                                            </option>
                                            <option value="ekor"
                                                {{ old('satuan', $produk->satuan) == 'ekor' ? 'selected' : '' }}>ekor (Ayam
                                                Utuh)</option>
                                            <option value="paket"
                                                {{ old('satuan', $produk->satuan) == 'paket' ? 'selected' : '' }}>paket
                                                (Paket)</option>
                                        </select>
                                        @error('satuan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="gambar">Gambar Produk</label>
                                <div class="custom-file">
                                    <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                        id="gambar" name="gambar" accept="image/*">
                                    <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB</small>
                                    @error('gambar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if ($produk->gambar)
                                <div class="my-3" id="current-image">
                                    <h6>Gambar Saat Ini:</h6>
                                    <img src="{{ asset('storage/' . $produk->gambar) }}" class="img-thumbnail"
                                        style="max-height: 200px">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="delete_image"
                                            name="delete_image">
                                        <label class="form-check-label text-danger" for="delete_image">
                                            Hapus gambar ini
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Update Produk</button>
                                <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
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
        // Preview gambar saat dipilih
        document.getElementById('gambar').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Jika sudah ada preview sebelumnya, hapus
                if (document.querySelector('#preview-container')) {
                    document.querySelector('#preview-container').remove();
                }

                // Buat elemen preview
                const preview = document.createElement('div');
                preview.id = 'preview-container';
                preview.classList.add('mt-3');
                preview.innerHTML = `
                <h6>Preview Gambar Baru:</h6>
                <img src="${event.target.result}" class="img-thumbnail" style="max-height: 200px">
            `;

                // Tambahkan setelah input file
                document.querySelector('.custom-file').after(preview);
            }
            reader.readAsDataURL(e.target.files[0]);
        });

        // Jika checkbox "hapus gambar" dicentang, sembunyikan gambar saat ini
        if (document.getElementById('delete_image')) {
            document.getElementById('delete_image').addEventListener('change', function() {
                const currentImage = document.getElementById('current-image');
                if (this.checked) {
                    currentImage.style.opacity = '0.5';
                } else {
                    currentImage.style.opacity = '1';
                }
            });
        }
    </script>
@endpush
