@extends('layouts.app')

@section('title', 'Tambah Produk Baru')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Produk Baru</h4>
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

                        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('nama_produk') is-invalid @enderror" id="nama_produk"
                                            name="nama_produk" value="{{ old('nama_produk') }}" required>
                                        @error('nama_produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="harga">Harga (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('harga') is-invalid @enderror"
                                            id="harga" name="harga" value="{{ old('harga') }}" min="0"
                                            step="100" required>
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
                                            id="stok" name="stok" value="{{ old('stok', 0) }}" min="0"
                                            required>
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
                                            <option value="pcs" {{ old('satuan') == 'pcs' ? 'selected' : '' }}>pcs
                                                (Potong)</option>
                                            <option value="Kg" {{ old('satuan') == 'Kg' ? 'selected' : '' }}>Kg
                                                (Kilogram)</option>
                                            <option value="ekor" {{ old('satuan') == 'ekor' ? 'selected' : '' }}>ekor
                                                (Ayam Utuh)</option>
                                            <option value="paket" {{ old('satuan') == 'paket' ? 'selected' : '' }}>paket
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

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Produk</button>
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
                <h6>Preview Gambar:</h6>
                <img src="${event.target.result}" class="img-thumbnail" style="max-height: 200px">
            `;

                // Tambahkan setelah input file
                document.querySelector('.custom-file').after(preview);
            }
            reader.readAsDataURL(e.target.files[0]);
        });
    </script>
@endpush
