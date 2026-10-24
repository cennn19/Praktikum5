@extends('layouts.app')

@section('title', 'Login')
@section('hero_title', 'Login Admin')
@section('hero_subtitle', 'Masuk untuk mengelola berita Desa Jalatrang')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-berita p-4">

                <h5 class="mb-4 fw-bold">Masuk ke Akun Anda</h5>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn w-100 btn-baca fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>

                </form>

            </div>
        </div>
    </div>
@endsection