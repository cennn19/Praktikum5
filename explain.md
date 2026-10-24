# Alur Program: Dari Request Browser Hingga Relasi Tampil

Dokumen ini hanya membahas satu hal: **bagaimana data dari beberapa tabel yang saling berelasi bisa muncul di browser**. Dari saat user buka URL, sampai halaman terbentuk.

---

## Gambaran Besar

```
Browser → routes/web.php → BeritaController → Model (+ relasi) → Database → View → Browser
```

---

## Langkah per Langkah

### 1. Browser Mengirim Request

User membuka `http://localhost/berita` di browser. Request HTTP GET itu masuk ke `public/index.php`, yang langsung menyerahkan kendali ke Laravel.

---

### 2. Route Menentukan Tujuan

```php
// routes/web.php
Route::get('/berita', [BeritaController::class, 'index']);
```

Laravel mencocokkan URL `/berita` dengan baris ini, lalu memanggil method `index` di `BeritaController`.

---

### 3. Controller Meminta Data ke Model

```php
// BeritaController.php — method index()
$berita = Berita::with(['kategori', 'tags'])
    ->filter($request->only('q', 'kategori'))
    ->latest('tanggal')
    ->paginate(9)
    ->withQueryString();
```

Baris `with(['kategori', 'tags'])` adalah kuncinya. Ini memberitahu Eloquent untuk **langsung menarik data relasi sekalian dalam satu proses query**, bukan satu per satu saat ditampilkan. Teknik ini disebut *eager loading* — tanpanya, setiap kartu berita akan memicu query sendiri ke database hanya untuk mendapat nama kategori dan tag-nya.

---

### 4. Eloquent Menjalankan Query ke Database

Saat `with(['kategori', 'tags'])` dipanggil, Eloquent menjalankan tiga query sekaligus:

**Query 1** — ambil daftar berita:
```sql
SELECT * FROM berita ORDER BY tanggal DESC LIMIT 9;
```

**Query 2** — ambil kategori yang dipakai berita-berita tadi (relasi `belongsTo`):
```sql
SELECT * FROM kategori WHERE id IN (1, 2, 3, ...);
```

**Query 3** — ambil tag lewat tabel pivot `berita_tag` (relasi `belongsToMany`):
```sql
SELECT tags.*, berita_tag.berita_id
FROM tags
INNER JOIN berita_tag ON tags.id = berita_tag.tag_id
WHERE berita_tag.berita_id IN (1, 2, 3, ...);
```

Eloquent lalu **mencocokkan hasilnya di PHP** — setiap objek `$berita` sudah disisipkan data `kategori` dan `tags` yang sesuai, siap dipakai tanpa query tambahan.

---

### 5. Relasi di Model Menjadi "Jembatan"

Supaya langkah 4 bisa terjadi, model harus mendefinisikan relasinya lebih dulu.

**Model Berita** (`app/Models/Berita.php`) punya:
```php
// Satu berita punya satu kategori
public function kategori() {
    return $this->belongsTo(Kategori::class);
}

// Satu berita punya banyak tag (via tabel pivot berita_tag)
public function tags() {
    return $this->belongsToMany(Tag::class, 'berita_tag');
}

// Satu berita punya banyak komentar
public function komentar() {
    return $this->hasMany(Komentar::class);
}

// Satu berita ditulis oleh satu user
public function user() {
    return $this->belongsTo(User::class);
}
```

Method-method ini yang dibaca Eloquent ketika `with(['kategori', 'tags'])` dipanggil. Laravel tahu harus join ke tabel mana dan pakai kolom apa sebagai penghubung karena definisi relasi ini.

---

### 6. Controller Mengirim Data ke View

Setelah data siap, controller meneruskannya ke file Blade:

```php
return view('berita.index', compact('berita', 'kategori', 'tags'));
```

Variabel `$berita` yang dikirim ini sudah berisi koleksi berita lengkap — tiap objek sudah menyertakan data kategori dan tag-nya.

---

### 7. View Menampilkan Data Relasi

Di `resources/views/berita/index.blade.php`, data relasi tinggal diakses lewat properti:

```blade
{{-- Nama kategori dari relasi belongsTo --}}
<span class="badge">{{ $b->kategori->nama }}</span>

{{-- Daftar tag dari relasi belongsToMany --}}
@foreach ($b->tags as $tag)
    <span class="tag-pill">#{{ $tag->nama }}</span>
@endforeach
```

Blade mengeksekusi kode PHP di atas, menghasilkan HTML murni, lalu mengirimnya ke browser sebagai response.

---

### 8. Di Halaman Detail (show)

Alurnya sama, hanya relasi yang dimuat berbeda. URL `/berita/slug-artikel` ditangkap route:

```php
Route::get('/berita/{berita}', [BeritaController::class, 'show']);
```

Laravel langsung mencari objek `Berita` berdasarkan slug (bukan ID) karena model punya:
```php
public function getRouteKeyName() {
    return 'slug';
}
```

Controller `show` lalu memuat relasi yang dibutuhkan halaman detail:
```php
$berita->load(['kategori', 'tags', 'user']);
$komentar = $berita->komentar()->latest()->get();
```

Di view `show.blade.php`, hasilnya bisa langsung dipakai:
```blade
{{-- Nama kategori --}}
{{ $berita->kategori->nama }}

{{-- Nama penulis dari relasi user, fallback ke kolom teks jika kosong --}}
{{ $berita->user->name ?? $berita->penulis }}

{{-- Daftar tag --}}
@foreach ($berita->tags as $tag)
    <span>#{{ $tag->nama }}</span>
@endforeach

{{-- Daftar komentar --}}
@foreach ($komentar as $k)
    <div>{{ $k->nama }}: {{ $k->pesan }}</div>
@endforeach
```

---

## Ringkasan Alur

```
1. User buka URL
       ↓
2. routes/web.php → pilih Controller & method
       ↓
3. Controller panggil  Berita::with(['kategori', 'tags'])
       ↓
4. Eloquent jalankan query ke tabel berita, kategori, tags, berita_tag
       ↓
5. Hasil query dicocokkan & disisipkan ke tiap objek Berita
       ↓
6. Controller kirim data ke View
       ↓
7. Blade render HTML — akses $b->kategori->nama, $b->tags, dst.
       ↓
8. HTML dikirim ke browser → relasi tampil di layar
```

Relasi tampil di browser bukan karena keajaiban — tapi karena **definisi relasi di Model** memberi tahu Eloquent cara menarik data dari tabel lain, dan **`with()`** di Controller memastikan semua itu sudah siap sebelum View dirender.
