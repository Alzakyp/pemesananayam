<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     */
    public function index()
    {
        $users = User::all();
        return view('user.index', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Menyimpan data pengguna baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,pelanggan',
            'no_hp' => 'required|string|max:15',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('user.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pengguna.
     */
    public function show(User $user)
    {
        return view('user.show', compact('user'));
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    /**
     * Mengupdate data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,pelanggan',
            'no_hp' => 'required|string|max:15',
        ]);

        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('user.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    // public function dashboard()
    // {
    //     $jumlahPelanggan = User::where('role', 'pelanggan')->count();
    //     $jumlahTransaksi = 6; // Ubah sesuai data transaksi dari database
    //     $totalPendapatan = 1477000; // Ubah sesuai perhitungan pendapatan dari database

    //     return view('dashboard', compact('jumlahPelanggan', 'jumlahTransaksi', 'totalPendapatan'));
    // }

}
