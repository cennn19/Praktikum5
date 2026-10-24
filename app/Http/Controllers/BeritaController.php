<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Kategori;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $berita = Berita::with(['kategori', 'tags'])
            ->filter($request->only('q', 'kategori'))
            ->latest('tanggal')
            ->paginate(9)
            ->withQueryString();

        $kategori = Kategori::orderBy('nama')->get();
        $tags = Tag::orderBy('nama')->get();

        return view('berita.index', compact('berita', 'kategori', 'tags'));
    }

    public function create()
    {
        return view('berita.create', [
            'kategori' => Kategori::orderBy('nama')->get(),
            'tags'     => Tag::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'       => 'required|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'penulis'     => 'required|max:100',
            'ringkasan'   => 'required|max:300',
            'isi'         => 'required|min:10',
            'gambar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
            'tanggal'     => 'required|date',
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        $data['slug'] = Str::slug($data['judul']);

        $beritaBaru = $request->user()->beritas()->create(Arr::except($data, 'tags'));
        $beritaBaru->tags()->sync($data['tags'] ?? []);

        return redirect()->route('berita.index')
            ->with('success', 'Berita berhasil ditambahkan!');
    }

    public function show(Berita $berita)
    {
        $kunci = 'viewed_' . $berita->id;

        if (! session()->has($kunci)) {
            $berita->increment('views');
            session([$kunci => true]);
        }

        $berita->load(['kategori', 'tags', 'user']);

        $terkait = Berita::where('id', '!=', $berita->id)
            ->latest('tanggal')
            ->take(5)
            ->get();

        $komentar = $berita->komentar()->latest()->get();

        $angka1 = random_int(1, 9);
        $angka2 = random_int(1, 9);
        session(['captcha_answer' => $angka1 + $angka2]);

        return view('berita.show', compact('berita', 'terkait', 'komentar', 'angka1', 'angka2'));
    }

    public function edit(Berita $berita)
    {
        if (auth()->id() !== $berita->user_id && ! auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return view('berita.edit', [
            'berita'   => $berita->load('tags'),
            'kategori' => Kategori::orderBy('nama')->get(),
            'tags'     => Tag::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Berita $berita)
    {
        if (auth()->id() !== $berita->user_id && ! auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'judul'       => 'required|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'penulis'     => 'required|max:100',
            'ringkasan'   => 'required|max:300',
            'isi'         => 'required|min:10',
            'gambar'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
            'tanggal'     => 'required|date',
        ]);

        if ($request->hasFile('gambar')) {
            if ($berita->gambar) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        } else {
            unset($data['gambar']);
        }

        $data['slug'] = Str::slug($data['judul']);

        $berita->update(Arr::except($data, 'tags'));
        $berita->tags()->sync($data['tags'] ?? []);

        return redirect()->route('berita.index')
            ->with('success', 'Berita berhasil diperbarui!');
    }

    public function destroy(Berita $berita)
    {
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        return redirect()->route('berita.index')
            ->with('success', 'Berita berhasil dihapus!');
    }

    public function komentar(Request $request, Berita $berita)
    {
        $data = $request->validate([
            'nama'    => 'required|max:100',
            'email'   => 'required|email|max:100',
            'no_hp'   => 'required|max:20',
            'pesan'   => 'required|max:300',
            'captcha' => 'required|numeric',
        ]);

        if ((int) $data['captcha'] !== (int) session('captcha_answer')) {
            return back()
                ->withInput()
                ->withErrors(['captcha' => 'Jawaban captcha salah, coba lagi.']);
        }

        $berita->komentar()->create([
            'nama'  => $data['nama'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'],
            'pesan' => $data['pesan'],
        ]);

        return redirect()
            ->to(route('berita.show', $berita) . '#komentar')
            ->with('success', 'Komentar berhasil dikirim.');
    }
}