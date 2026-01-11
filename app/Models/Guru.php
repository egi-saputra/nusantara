<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'akses',
        'kaprog',
        'piket',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'guru_id', 'id');
    }

    public function mapel()
    {
        return $this->hasOne(Mapel::class, 'guru_id', 'id');
    }

    public function kejuruan()
    {
        return $this->hasOne(Kejuruan::class, 'guru_id', 'id');
    }

}
