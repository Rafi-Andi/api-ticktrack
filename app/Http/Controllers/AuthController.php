<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Casts\Json;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Email atau password salah',
                    'data' => null
                ], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'login gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profile()
    {

        try {
            $user = Auth::user();

            return response()->json([
                'message' => 'berhasil mengambil profile',
                'data' => [
                    'user' => new UserResource($user)
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'gagal mengambil profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Berhasil logout',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'logout gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|min:5|max:40',
            'email' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);

        DB::beginTransaction();
        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->role = 'user';
            $user->password = Hash::make($data['password']);
            $user->save();
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Register berhasil',
                'data' => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'logout gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
