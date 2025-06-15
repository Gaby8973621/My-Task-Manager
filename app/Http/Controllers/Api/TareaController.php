<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    // Método para listar tareas
    public function index(Request $request)
    {
        $user = auth()->user();

        // Si el usuario tiene permisos para ver todas las tareas o es Super Admin
        if ($user->can('ver_todas_las_tareas') || $user->hasRole('Super Admin')) {
            // Consulta todas las tareas
            $query = Tarea::query();
        } else {
            $query = $user->tareas();
        }

        if ($request->has('completada')) {
            $query->where('completada', filter_var($request->completada, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('buscar')) {
            $query->where('titulo', 'like', '%' . $request->buscar . '%');
        }

        $query->orderBy('created_at', $request->get('orden', 'desc'));

        return response()->json(['data' => $query->get()]);
    }

    // Método para crear una nueva tarea
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->can('crear_tarea') && !$user->hasRole('Super Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Validación de datos
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'completada' => 'boolean'
        ]);

        // Crea la tarea asociada al usuario autenticado
        $tarea = $user->tareas()->create($validated);

        return response()->json($tarea, 201);
    }

    // Método para mostrar una tarea específica
    public function show(string $id)
    {
        $tarea = Tarea::findOrFail($id);
        $user = auth()->user();

        if ($tarea->user_id !== $user->id && !$user->can('ver_todas_las_tareas') && !$user->hasRole('Super Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($tarea);
    }

    // Método para actualizar una tarea
    public function update(Request $request, string $id)
    {
        $tarea = Tarea::findOrFail($id);
        $user = auth()->user();

        if ($tarea->user_id !== $user->id && !$user->can('editar_tarea') && !$user->hasRole('Super Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'completada' => 'boolean'
        ]);

        $tarea->update($validated);

        return response()->json($tarea);
    }

    // Método para eliminar una tarea
    public function destroy(string $id)
    {
        $tarea = Tarea::findOrFail($id);
        $user = auth()->user();

        // Verifica si el usuario puede eliminar esta tarea
        if ($tarea->user_id !== $user->id && !$user->can('eliminar_tarea') && !$user->hasRole('Super Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Elimina la tarea
        $tarea->delete();

        return response()->json(['mensaje' => 'Tarea eliminada'], 204); // 204: No Content
    }
}
