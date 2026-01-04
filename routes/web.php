<?php

use App\Http\Controllers\CentralController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\MadingController;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/central-login', [CentralController::class, 'showLoginForm'])->name('auth.central.login');
Route::post('/central-login', [CentralController::class, 'central'])->name('auth.central');

Route::resource('mading', MadingController::class)->only(['index']);
Route::get('/mading/{id}', [MadingController::class, 'show'])
    ->name('mading.show');

Route::middleware(['auth'])->group(function () {
    Route::delete('/pengumuman/delete-all', [PengumumanController::class, 'deleteAll'])->name('pengumuman.deleteAll');

    Route::resource('pengumuman', PengumumanController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::get('/pengumuman/mine', function() {
        return \App\Models\Pengumuman::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    });
});

// Verifikasi Email
// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware(['auth'])->name('verification.notice');

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'guru'    => redirect()->route('guru.dashboard'),
        'proktor' => redirect()->route('proktor.dashboard'),
        'siswa'   => redirect()->route('siswa.dashboard'),
        'user'    => redirect()->route('user.dashboard'),
        default   => abort(403),
    };
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth', 'verified')->group(function () {
    Route::get('/admin/dashboard', function () {
        $user = Auth::user();

        $usersCount = [
            'admin'   => User::where('role', 'admin')->count(),
            'proktor' => User::where('role', 'proktor')->count(),
            'guru'    => User::where('role', 'guru')->count(),
            'siswa'   => User::where('role', 'siswa')->count(),
            'total'   => User::count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'auth' => [
                'user' => $user,
                'role' => $user->role,
            ],
            'usersCount' => $usersCount,
        ]);
    })->name('admin.dashboard')->middleware(['auth']);

    Route::get('/guru/dashboard', function () {
        $user = Auth::user();

        $usersCount = [
            'admin'   => User::where('role', 'admin')->count(),
            'proktor' => User::where('role', 'proktor')->count(),
            'guru'    => User::where('role', 'guru')->count(),
            'siswa'   => User::where('role', 'siswa')->count(),
            'total'   => User::count(),
        ];

        return Inertia::render('Guru/Dashboard', [
            'auth' => [
                'user' => $user,
                'role' => $user->role,
            ],
            'usersCount' => $usersCount,
        ]);
    })->name('guru.dashboard')->middleware(['auth']);

    Route::get('/proktor/dashboard', function () {
        $user = Auth::user();

        $usersCount = [
            'admin'   => User::where('role', 'admin')->count(),
            'proktor' => User::where('role', 'proktor')->count(),
            'guru'    => User::where('role', 'guru')->count(),
            'siswa'   => User::where('role', 'siswa')->count(),
            'total'   => User::count(),
        ];

        return Inertia::render('Proktor/Dashboard', [
            'auth' => [
                'user' => $user,
                'role' => $user->role,
            ],
            'usersCount' => $usersCount,
        ]);
    })->name('proktor.dashboard')->middleware(['auth']);

    Route::get('/siswa/dashboard', function () {
        $user = Auth::user();

        // cek apakah siswa sudah punya data
        $siswaExists = Siswa::where('user_id', $user->id)->exists();

        if (!$siswaExists) {
            return redirect()->route('siswa.form.create');
        }

        // Ambil data siswa (misal semua siswa)
        $siswa = Siswa::with(['kelas', 'kejuruan'])
            ->where('user_id', $user->id)
            ->first();

        if (!$siswa) {
            return redirect()->route('siswa.form.create');
        }

        return Inertia::render('Siswa/Dashboard', [
            'siswa' => $siswa,
            'auth' => [
                'user' => $user,
                'role' => $user->role,
            ],
        ]);
    })->middleware(['auth'])->name('siswa.dashboard');

    Route::get('/user/dashboard', fn() =>
        Inertia::render('User/Dashboard')
    )->name('user.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth Default
require __DIR__.'/auth.php';

// import route terpisah
require __DIR__.'/admin.php';
require __DIR__.'/proktor.php';
require __DIR__.'/guru.php';
require __DIR__.'/siswa.php';
require __DIR__.'/user.php';