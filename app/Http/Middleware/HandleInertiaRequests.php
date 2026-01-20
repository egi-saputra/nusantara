<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Pengumuman;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'avatar' => $request->user()?->avatar,
                'role' => $request->user()?->role,
            ],
            'siswa' => fn () => $request->user()?->siswa,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Tambahkan ini untuk bell notifikasi
            'announcements' => function() use ($request) {
                $user = $request->user();
                if (!$user) return collect();

                $kelasId = $user->siswa?->kelas_id ?? null;
                $role = strtolower($user->role);

                return Pengumuman::latest()
                    ->get()
                    ->filter(function($item) use ($role, $kelasId) {
                        if ($item->penerima === 'semua') return true;           // Semua orang
                        if ($item->penerima === $role) return true;            // Role spesifik: admin/guru/proktor
                        if ($role === 'siswa' && $item->penerima === 'siswa') { // Siswa dengan kelas tertentu
                            return $item->kelas_id ? $item->kelas_id == $kelasId : true;
                        }
                        return false;
                    })
                    ->values();
            },
            'userClass' => $request->user()?->siswa?->kelas?->kelas ?? null,
            'kelasId' => $request->user()?->siswa?->kelas_id
                ? (int) $request->user()?->siswa->kelas_id
                : null,
        ]);
    }
}
