<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berita_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_id')->constrained('berita')->cascadeOnDelete(); // ← ubah 'beritas' kembali ke 'berita'← 'beritas' salah, harusnya 'berita'
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['berita_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_tag');
    }
};
