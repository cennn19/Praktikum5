@extends('layouts.app')

@section('title', 'Edit Berita')
@section('hero_title', 'Edit Berita')
@section('hero_subtitle', 'Perbarui informasi berita yang sudah ada')
@section('breadcrumb_extra', $berita->judul)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-berita p-4">

                <form action="{{ route('berita.update', $berita) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul</label>
                        <input
                            type="text"
                            name="judul"
                            class="form-control @error('judul') is-invalid @enderror"
                            value="{{ old('judul', $berita->judul) }}"
                        >
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}" {{ old('kategori_id', $berita->kategori_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Penulis</label>
                        <input
                            type="text"
                            name="penulis"
                            class="form-control @error('penulis') is-invalid @enderror"
                            value="{{ old('penulis', $berita->penulis) }}"
                        >
                        @error('penulis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ringkasan</label>
                        <textarea
                            name="ringkasan"
                            rows="2"
                            class="form-control @error('ringkasan') is-invalid @enderror"
                        >{{ old('ringkasan', $berita->ringkasan) }}</textarea>
                        @error('ringkasan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Isi Berita</label>
                        <textarea
                            name="isi"
                            rows="6"
                            class="form-control @error('isi') is-invalid @enderror"
                        >{{ old('isi', $berita->isi) }}</textarea>
                        @error('isi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gambar</label>
                        @if ($berita->gambar)
                            <div class="mb-2">
                                <img
                                    src="{{ Storage::url($berita->gambar) }}"
                                    alt="Gambar saat ini"
                                    style="height: 150px; object-fit: cover; border-radius: 0.5rem;"
                                >
                                <div class="small text-muted mt-1">Gambar saat ini. Upload baru untuk mengganti.</div>
                            </div>
                        @endif
                        <input
                            type="file"
                            name="gambar"
                            class="form-control @error('gambar') is-invalid @enderror"
                            accept="image/*"
                        >
                        @error('gambar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tags</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="tags[]"
                                        value="{{ $tag->id }}"
                                        id="tag_{{ $tag->id }}"
                                        class="form-check-input"
                                        {{ in_array($tag->id, old('tags', $berita->tags->pluck('id')->toArray())) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="tag_{{ $tag->id }}">
                                        {{ $tag->nama }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('tags')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tanggal</label>
                        <input
                            type="date"
                            name="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', $berita->tanggal?->format('Y-m-d')) }}"
                        >
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-baca fw-bold px-4">
                            <i class="bi bi-save"></i> Perbarui
                        </button>
                        <a href="{{ route('berita.index') }}" class="btn btn-outline-secondary px-4">
                            Batal
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection