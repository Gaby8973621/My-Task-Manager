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
    // Método para iniciar sesión
    public function login(Request $request)
    {
        $credential = $request->only('email', 'password');

        // Verifica credenciales
        if (!$token = JWTAuth::attempt($credential)) {
            return response()->json(['errors' => 'Credenciales inválidas'], 401);
        }

        $user = Auth::user();

        $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();

        //personalizados para el token JWT
        $customClaims = [
            'user_id' => $user->id,
            'email' => $user->email,
            'userInformation' => $userInformation ? $userInformation->toArray() : null,
            'role' => $user->getRoleNames()
        ];

        // Genera token personalizados
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        // Devuelve token y roles
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'role' => $user->getRoleNames()
        ]);
    }

    // Método para refrescar el token JWT
    public function refresh(Request $request)
    {
        try {
            $currentToken = JWTAuth::getToken();

            if (!$currentToken) {
                return response()->json(['error' => 'Token no proporcionado'], 400);
            }

            try {
                // Intenta obtener el usuario del token actual
                $user = JWTAuth::toUser($currentToken);
            } catch (TokenExpiredException $e) {
                // Si el token expiró, refrescarlo
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

            // Genera un nuevo token
            $newToken = JWTAuth::claims($customClaims)->refresh($currentToken);

            return response()->json([
                'token' => $newToken,
                'message' => 'Token actualizado',
            ], 200);

        } catch (\Exception $e) {
            // Captura errores
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Método para registrar un nuevo usuario
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validación de datos del formulario
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|min:8|same:password'
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 400);
            }

            $emailExplode = explode('@', $request->email);
            $name = $emailExplode[0] . Str::random();

            // Crea el usuario
            $user = User::create([
                'name' => $name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asigna el rol 'User' por defecto
            $user->assignRole('User');

            $userInformation = MntPersonalInformationUserModel::where('user_id', $user->id)->first();

            $customClaims = [
                'user_id' => $user->id,
                'email' => $user->email,
                'userInformation' => $userInformation ? $userInformation->toArray() : null,
                'role' => $user->getRoleNames()
            ];

            // Genera token
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado',
                'token' => $token,
                'role' => $user->getRoleNames()
            ], 201);

        } catch (\Exception $th) {
            // Si ocurre un error
            return ApiResponse::error('Error al crear el usuario ' . $th->getMessage(), 500);
        }
    }

    // Método para cerrar sesión
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
