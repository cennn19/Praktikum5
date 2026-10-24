<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';

    protected $fillable = [
        'user_id', 'kategori_id', 'judul', 'slug', 'penulis', 'ringkasan',
        'isi', 'gambar', 'tags', 'views', 'tanggal', // ← tambahkan 'tags' disini
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function komentar()
    {
        return $this->hasMany(Komentar::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'berita_tag');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['q'] ?? null, function ($q, $cari) {
            $q->where(fn ($q) => $q->where('judul', 'like', "%{$cari}%")
                                   ->orWhere('ringkasan', 'like', "%{$cari}%"));
        });

        $query->when($filters['kategori'] ?? null, fn ($q, $id) => $q->where('kategori_id', $id));
    }
}