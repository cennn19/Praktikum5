{{-- Halaman daftar berita: menyediakan filter, kartu artikel, dan navigasi halaman. --}}
@extends('layouts.app')

@section('title', 'Berita & Informasi')

@section('content')
<div class="row g-4">

    {{-- Sidebar filter --}}
    <div class="col-lg-3">
        <div class="filter-box">
            <div class="filter-header">
                <i class="bi bi-funnel-fill me-2"></i>Filter Berita
            </div>

            <div class="filter-body">
                <form method="GET" action="{{ route('berita.index') }}">
                    <label class="fw-semibold mb-1">Kata Kunci</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control mb-3" placeholder="Cari berita...">

                    <label class="fw-semibold mb-1">Kategori</label>
                    <select name="kategori" class="form-select mb-3">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary w-100 mb-2">Cari</button>
                    <a href="{{ route('berita.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </form>

                {{-- Tombol Tambah Berita --}}
                @auth
                    <hr>
                    <a href="{{ route('berita.create') }}" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle"></i> Tambah Berita
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Grid berita --}}
    <div class="col-lg-9">
        <div class="row g-4">
            @forelse ($berita as $b)
                <div class="col-md-6 col-xl-4">
                    <div class="card-berita h-100 d-flex flex-column">

                        <img class="thumb" src="{{ Storage::url($b->gambar) }}" alt="{{ $b->judul }}"
                             onerror="this.src='https://placehold.co/600x240?text=No+Image'">

                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <div class="mb-2">
                                <span class="badge badge-kategori">{{ $b->kategori->nama }}</span>
                                <small class="text-muted ms-2">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $b->tanggal->translatedFormat('d M Y') }}
                                </small>
                            </div>

                            <h5 class="fw-bold">{{ \Illuminate\Support\Str::limit($b->judul, 60) }}</h5>
                            <p class="text-muted small">{{ \Illuminate\Support\Str::limit($b->ringkasan, 90) }}</p>

                            <div class="mb-3">
                                @foreach ($b->tags as $tag)
                                    <span class="tag-pill">#{{ $tag->nama }}</span>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="text-muted"><i class="bi bi-eye-fill"></i> {{ $b->views }}</span>
                                <a href="{{ route('berita.show', $b) }}" class="btn btn-sm btn-baca">
                                    Baca <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>

                            {{-- Tombol Edit & Hapus --}}
                            @auth
                                <div class="mt-2 pt-2 border-top d-flex gap-2">
                                    <a href="{{ route('berita.edit', $b) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                        <form action="{{ route('berita.destroy', $b) }}" method="POST"
                                              onsubmit="return confirm('Yakin hapus berita ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                </div>
                            @endauth

                        </div>

                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-0">Berita tidak ditemukan.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $berita->links() }}</div>
    </div>

</div>
@endsection