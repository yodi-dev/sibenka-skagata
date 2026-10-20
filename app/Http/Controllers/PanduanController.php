<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanduanController extends Controller
{
    /**
     * Dispatcher cerdas: Mengarahkan pengguna ke halaman panduan spesifik perannya.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'waka') {
            return redirect()->route('superadmin.panduan');
        }

        if ($user->role === 'toolman') {
            return redirect()->route('toolman.panduan');
        }

        return redirect()->route('peminjam.panduan');
    }

    /**
     * Halaman panduan untuk Peminjam (Siswa & Guru).
     */
    public function peminjam()
    {
        $user = auth()->user();
        return view('peminjam.panduan.index', compact('user'));
    }

    /**
     * Halaman panduan untuk Admin Bengkel (Toolman).
     */
    public function toolman()
    {
        $user = auth()->user();
        $bengkel = $user->bengkel;
        return view('toolman.panduan.index', compact('user', 'bengkel'));
    }

    /**
     * Halaman panduan untuk Super Admin (Waka Sarpras).
     */
    public function superadmin()
    {
        $user = auth()->user();
        return view('superadmin.panduan.index', compact('user'));
    }
}
