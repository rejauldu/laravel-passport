<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    function register(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['min:8', 'confirmed']
        ]);
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);
        $token = $user->createToken('auth_token')->accessToken;
        return response()->json(
          [
              'token' => $token,
              'user' => $user,
              'message' => 'User created successfully',
              'status' => 1
          ]
        );
    }
}
