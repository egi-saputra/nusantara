<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'pengumuman',
        'penerima',
        'kelas_id',
        'user_id',
    ];

    /**
     * Relasi ke pembuat pengumuman
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke kelas (jika penerima siswa kelas tertentu)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
