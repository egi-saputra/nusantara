<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return inertia('Admin/Users/Index', [
            'users' => User::where('role', '!=', 'admin')
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => ['proktor', 'guru', 'siswa', 'user'], // ← langsung hardcoded
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                'unique:users,email',
                // hanya boleh gmail.com & nusantara .com
                // 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com|nusantara\.com)$/'
            ],
            'password' => 'required|min:6',
            'role'     => [
                'required',
                'string',
                Rule::notIn(['admin']),
            ],
        ],
        // ['email.regex' => 'Email must use the @gmail.com or valid domain.',]
        );

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User has been created!');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user'  => $user,
            'roles' => ['proktor', 'guru', 'siswa', 'user'],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
                // 'regex:/^[a-zA-Z0-9._%+-]+@(gmail\.com)$/',
            ],          
            'password' => 'nullable|min:6',
            'role'     => [
                'required',
                'string',
                Rule::notIn(['admin']),
            ],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User has been updated!');
    }

    // Hapus user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User has been deleted');
    }
}
