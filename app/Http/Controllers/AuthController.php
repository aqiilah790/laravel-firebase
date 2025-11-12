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

    // --- PROSES REGISTER ---
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'name' => 'required'
        ]);

        try {
            $createdUser = $this->auth->createUserWithEmailAndPassword($request->email, $request->password);
            $firebaseUser = $this->auth->getUserByEmail($request->email);
            $uid = $firebaseUser->uid;

            $this->auth->updateUser($uid, ['displayName' => $request->name]);
            $this->auth->sendEmailVerificationLink($request->email);

            // Simpan data awal mahasiswa
            $this->database->getReference('mahasiswa')->push([
                'uid' => $uid,
                'nama' => $request->name,
                'email' => $request->email,
                'nim' => '',
                'jurusan' => '',
                'angkatan' => '',
                'verified' => false,
            ]);

            return redirect('/verifikasi')->with('success', 'Registrasi berhasil! Cek email untuk verifikasi.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    // --- FORM LOGIN ---
    public function showLogin()
    {
        return view('auth.login');
    }

    // --- PROSES LOGIN ---
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
                return back()->with('error', 'Email belum diverifikasi.');
            }

            // Ambil data mahasiswa dari database
            $ref = $this->database->getReference('mahasiswa');
            $data = $ref->orderByChild('uid')->equalTo($uid)->getValue();
            $mahasiswa = $data ? reset($data) : null;

            // Jika belum lengkapi data, arahkan ke halaman lengkapi
            if (!$mahasiswa || empty($mahasiswa['nim']) || empty($mahasiswa['jurusan']) || empty($mahasiswa['angkatan']) || !$mahasiswa['verified']) {
                session(['firebase_user' => [
                    'uid' => $uid,
                    'email' => $user->email,
                    'displayName' => $user->displayName,
                ]]);
                return redirect('/lengkapi-data')->with('info', 'Silakan lengkapi data terlebih dahulu.');
            }

            // Jika lengkap, simpan session dan arahkan ke mahasiswa
            session(['firebase_user' => [
                'uid' => $uid,
                'email' => $user->email,
                'displayName' => $user->displayName,
            ]]);

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

        $ref = $this->database->getReference('mahasiswa');
        $data = $ref->orderByChild('uid')->equalTo($uid)->getValue();

        if ($data) {
            $key = array_key_first($data);
            $ref->getChild($key)->update([
                'nim' => $request->nim,
                'jurusan' => $request->jurusan,
                'angkatan' => $request->angkatan,
                'verified' => true,
            ]);
        }

        return redirect('/mahasiswa')->with('success', 'Data berhasil dilengkapi!');
    }

    // --- LOGOUT ---
    public function logout()
    {
        session()->forget('firebase_user');
        return redirect('/login')->with('success', 'Berhasil logout.');
    }
}
