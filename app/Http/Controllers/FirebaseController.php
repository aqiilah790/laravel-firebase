<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class FirebaseController extends Controller
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->database = $factory->createDatabase();
    }

    public function index()
    {
        $data = $this->database->getReference('users')->getValue();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $postData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        $this->database->getReference('users')->push($postData);
        return response()->json(['message' => 'User added successfully']);
    }

    public function update(Request $request, $id)
    {
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        $this->database->getReference('users/' . $id)->update($updateData);
        return response()->json(['message' => 'User updated successfully']);
    }

    public function delete($id)
    {
        $this->database->getReference('users/' . $id)->remove();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
