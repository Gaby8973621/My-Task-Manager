<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\MntPersonalInformationUserModel;
use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credential = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credential)) {
            return response()->json(['errors' => 'Credenciales inválidas'], 401);
        }

        $user = Auth::user();
        $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();

        $customClaims = [
            'user_id' => $user->id,
            'email' => $user->email,
            'userInformation' => $userInformation ? $userInformation->toArray() : null,
            'role' => $user->getRoleNames()
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'token' => $token,
            'role' => $user->getRoleNames()
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $currentToken = JWTAuth::getToken();

            if (!$currentToken) {
                return response()->json(['error' => 'Token no proporcionado'], 400);
            }

            try {
                $user = JWTAuth::toUser($currentToken);
            } catch (TokenExpiredException $e) {
                $currentToken = JWTAuth::refresh($currentToken);
                $user = JWTAuth::toUser($currentToken);
            }

            $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();

            $customClaims = [
                'user_id' => $user->id,
                'email' => $user->email,
                'userInformation' => $userInformation ? $userInformation->toArray() : null,
                'role' => $user->getRoleNames()
            ];

            $newToken = JWTAuth::claims($customClaims)->refresh($currentToken);

            return response()->json([
                'token' => $newToken,
                'message' => 'Token actualizado',
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|min:8|same:password'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 400);
            }

            $emailExplode = explode('@', $request->email);
            $name = $emailExplode[0] . Str::random();

            $user = User::create([
                'name' => $name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('User');

            $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();

            $customClaims = [
                'user_id' => $user->id,
                'email' => $user->email,
                'userInformation' => $userInformation ? $userInformation->toArray() : null,
                'role' => $user->getRoleNames()
            ];

            $token = JWTAuth::claims($customClaims)->fromUser($user);

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado',
                'token' => $token,
                'role' => $user->getRoleNames()
            ], 201);

        } catch (\Exception $th) {
            return ApiResponse::error('Error al crear el usuario ' . $th->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json([
                    'message' => 'No token provided',
                    'status' => 400
                ], 400);
            }

            $user = JWTAuth::authenticate($token);
            $user->update(['is_logged_in' => false]);

            return response()->json([
                'message' => 'Sesión cerrada exitosamente',
                'status' => 200
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Error invalidando el token: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
