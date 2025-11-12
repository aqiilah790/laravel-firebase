<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\DatabaseException;

class MahasiswaController extends Controller
{
    protected $database;
    protected $table = 'mahasiswa';

    public function __construct()
    {
        // Pastikan path kredensial benar
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'storage/app/firebase/project-laravel-f5576-firebase-adminsdk-fbsvc-041c555635.json'));
        $databaseUrl = env('FIREBASE_DATABASE_URL');

        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials file not found at: {$credentialsPath}");
        }

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri($databaseUrl);

        $this->database = $factory->createDatabase();
    }

    // Ambil semua data mahasiswa
    public function index()
    {
        try {
            $data = $this->database->getReference($this->table)->getValue();
            return response()->json($data ?? []);
        } catch (DatabaseException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Tambah data mahasiswa
    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            'jurusan' => 'required|string|max:100',
            'angkatan' => 'required|integer',
            'email' => 'required|email'
        ]);

        try {
            $newMahasiswa = [
                'nim' => $request->nim,
                'nama' => $request->nama,
                'jurusan' => $request->jurusan,
                'angkatan' => $request->angkatan,
                'email' => $request->email,
            ];

            $this->database->getReference($this->table)->push($newMahasiswa);

            return response()->json(['message' => 'Mahasiswa berhasil ditambahkan']);
        } catch (DatabaseException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update data mahasiswa
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jurusan' => 'required|string|max:100',
            'angkatan' => 'required|integer',
            'email' => 'required|email'
        ]);

        try {
            $updateData = [
                'nama' => $request->nama,
                'jurusan' => $request->jurusan,
                'angkatan' => $request->angkatan,
                'email' => $request->email,
            ];

            $this->database->getReference("{$this->table}/{$id}")->update($updateData);

            return response()->json(['message' => 'Data mahasiswa diperbarui']);
        } catch (DatabaseException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Hapus data mahasiswa
    public function destroy($id)
    {
        try {
            $this->database->getReference("{$this->table}/{$id}")->remove();
            return response()->json(['message' => 'Data mahasiswa dihapus']);
        } catch (DatabaseException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
