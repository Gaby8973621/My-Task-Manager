<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\TareaController;
use App\Http\Controllers\Api\CreatePermissionRolController;
use App\Http\Controllers\AuthController;

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh-token', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
});

// Rutas protegidas por JWT (auth:api)
Route::middleware('auth:api')->group(function () {

    // Rutas para gestión de usuarios y roles
    Route::prefix('users')->group(function () {
        Route::get('role', [CreatePermissionRolController::class, 'getRole'])->middleware('rol:Super Admin');
        Route::post('permissions', [CreatePermissionRolController::class, 'createPermissionsAction'])->middleware('rol:Super Admin,Admin');
        Route::post('role', [CreatePermissionRolController::class, 'store'])->middleware('rol:Super Admin');
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Ruta protegida solo para admin/super-admin
    Route::get('admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    })->middleware('rol:Admin,Super Admin');

    // ✅ Ruta para tareas (correctamente importada y protegida)
    Route::apiResource('tareas', TareaController::class);
});
