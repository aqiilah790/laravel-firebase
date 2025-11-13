<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class AuthController extends Controller
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

    // --- FORM REGISTER ---
    public function showRegister()
    {
        return view('auth.register');
    }

    // --- FORM LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    // --- REGISTER USER ---
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'name' => 'required'
        ]);

        try {
            // Buat user baru di Firebase Auth
            $createdUser = $this->auth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'displayName' => $request->name,
            ]);

            $uid = $createdUser->uid;

            // Kirim email verifikasi
            $this->auth->sendEmailVerificationLink($request->email);

            // Simpan data awal ke Realtime Database pakai UID sebagai key
            $this->database->getReference('mahasiswa/' . $uid)->set([
                'uid' => $uid,
                'nama' => $request->name,
                'email' => $request->email,
                'nim' => '',
                'jurusan' => '',
                'angkatan' => '',
                'verified' => false,
            ]);

            return redirect('/verifikasi')->with('success', 'Registrasi berhasil! Silakan cek email untuk verifikasi.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    // --- LOGIN USER ---
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($request->email, $request->password);
            $idToken = $signInResult->idToken();
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $this->auth->getUser($uid);

            if (!$user->emailVerified) {
                return back()->with('error', 'Email belum diverifikasi. Silakan cek inbox kamu.');
            }

            // Simpan session
            session(['firebase_user' => [
                'uid' => $uid,
                'email' => $user->email,
                'displayName' => $user->displayName,
            ]]);

            // Cek apakah data mahasiswa sudah lengkap
            $data = $this->database->getReference('mahasiswa/' . $uid)->getValue();
            if ($data) {
                if (empty($data) || !$data['verified']) {
                    return redirect('/lengkapi-data');
                }
            }

            return redirect('/mahasiswa');
        } catch (\Throwable $e) {
            return back()->with('error', 'Login gagal: ' . $e->getMessage());
        }
    }

    // --- FORM LENGKAPI DATA ---
    public function showLengkapiData()
    {
        if (!session('firebase_user')) {
            return redirect('/login');
        }

        return view('auth.lengkapi-data');
    }

    // --- PROSES LENGKAPI DATA ---
    public function lengkapiData(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'jurusan' => 'required',
            'angkatan' => 'required',
        ]);

        $user = session('firebase_user');
        $uid = $user['uid'];

        $this->database->getReference('mahasiswa/' . $uid)->update([
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
            'angkatan' => $request->angkatan,
            'verified' => true,
        ]);

        return redirect('/mahasiswa')->with('success', 'Data berhasil dilengkapi!');
    }

    // --- LOGOUT USER ---
    public function logout()
    {
        session()->forget('firebase_user');
        return redirect('/login')->with('success', 'Berhasil logout.');
    }
}
