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

            // Simpan data mahasiswa dengan key = UID
            $this->database->getReference("mahasiswa/{$uid}")->set([
                'uid' => $uid,
                'nama' => $request->nama,
                'email' => $request->email,
                'nim' => $request->nim ?? '',
                'jurusan' => $request->jurusan ?? '',
                'angkatan' => $request->angkatan ?? '',
                'verified' => true
            ]);

            return response()->json(['success' => 'User berhasil ditambahkan!']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // Update user via AJAX
    public function updateUser(Request $request)
    {
        $id = $request->id; // ini harus UID Firebase Auth

        try {
            // Update di Firebase Authentication
            $updateAuthData = [
                'displayName' => $request->nama,
                'email' => $request->email,
            ];

            if (!empty($request->password)) {
                $updateAuthData['password'] = $request->password;
            }

            // Update data Auth (kalau user masih ada)
            $this->auth->updateUser($id, $updateAuthData);

            // Update di Realtime Database
            $updateDB = [
                'nama' => $request->nama,
                'email' => $request->email,
                'nim' => $request->nim ?? '',
                'jurusan' => $request->jurusan ?? '',
                'angkatan' => $request->angkatan ?? '',
            ];

            $this->database->getReference("mahasiswa/{$id}")->update($updateDB);

            return response()->json(['success' => 'Data berhasil diperbarui!']);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function hapusUser(Request $request)
{
    $uid = $request->id;

    try {
        // Hapus user dari Firebase Authentication
        $this->auth->deleteUser($uid);

        // Hapus data dari Realtime Database
        $this->database->getReference('mahasiswa/' . $uid)->remove();

        return response()->json(['success' => 'User berhasil dihapus.']);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Gagal menghapus user: ' . $e->getMessage()]);
    }
}

}
