<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    function login(Request $request) {
        $validatedData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        $user = User::where('email', $validatedData['email'])->first();

        if(Hash::check($validatedData['password'], $user->password)) {
            $token = $user->createToken('auth_token')->accessToken;
            return response()->json(
                [
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Logged in successfully',
                    'status' => 1
                ]
            );
        }
        return response()->json(
            [
                'message' => 'Email or Password does not match',
                'status' => 0
            ]
        );
    }
}
