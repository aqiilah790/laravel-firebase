<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class AdminController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    // Halaman index mahasiswa
    public function index()
    {
        if (!session('firebase_user')) {
            return redirect('/login');
        }

        return view('admin.index');
    }

    // Tambah user baru via AJAX
    public function tambahUser(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'nim' => 'nullable',
            'jurusan' => 'nullable',
            'angkatan' => 'nullable'
        ]);

        try {
            // Buat user di Firebase Auth
            $user = $this->auth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'displayName' => $request->nama,
            ]);

            $uid = $user->uid;

            // Kirim email verifikasi
            $this->auth->sendEmailVerificationLink($request->email);

            // Simpan data awal di database
            $this->database->getReference('mahasiswa')->push([
                'uid' => $uid,
                'nama' => $request->nama,
                'email' => $request->email,
                'nim' => $request->nim ?? '',
                'jurusan' => $request->jurusan ?? '',
                'angkatan' => $request->angkatan ?? '',
                'verified' => false
            ]);

            return response()->json(['success' => 'User berhasil ditambahkan.']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
