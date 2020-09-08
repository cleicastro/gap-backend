<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class AuthenticationController extends Controller
{

    function register(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'type' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'password' => bcrypt($request->password)
        ]);

        $user->save();

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso!'
        ], 201);
    }

    function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Acesso negado, verifique suas credenciais'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('Access Token')->accessToken;

        return response()->json([
            'token' => $token,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
        ], 200);
    }

    function logout(Request $request) {
        // $request->user()->token()->revoke();
        Auth::logout();
        return response()->json([
            'message' => 'Usuário deslogado'
        ]);
    }
}
