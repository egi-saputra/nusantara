<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul')->nullable();
            $table->text('pengumuman');

            /**
             * penerima:
             *  - semua  → semua user
             *  - guru   → semua guru
             *  - proktor → semua proktor
             *  - siswa  → semua siswa (kelas_id boleh null)
             */
            $table->string('penerima')->default('semua');

            /**
             * kelas_id dipakai jika:
             * penerima = 'siswa'
             * dan admin memilih kelas tertentu
             */
            $table->unsignedBigInteger('kelas_id')->nullable();

            $table->unsignedBigInteger('user_id'); // pembuat pengumuman
            $table->timestamps();

            $table->foreign('kelas_id')
                ->references('id')->on('kelas')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
