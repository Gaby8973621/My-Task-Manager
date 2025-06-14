<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Si es admin, puede ver todas las tareas
        if ($user->hasRole('Admin')) {
            $query = Tarea::query(); // todas
        } else {
            $query = $user->tareas(); // solo propias
        }

        if ($request->has('completada')) {
            $query->where('completada', filter_var($request->completada, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('buscar')) {
            $query->where('titulo', 'like', '%' . $request->buscar . '%');
        }

        $orden = $request->get('orden', 'desc');
        $query->orderBy('created_at', $orden);

        return response()->json(
            $query->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'completada' => 'boolean'
        ]);

        $tarea = Auth::user()->tareas()->create($validated);

        return response()->json($tarea, 201);
    }

    public function show(string $id)
    {
        $tarea = Tarea::findOrFail($id);

        if ($tarea->user_id !== Auth::id() && !auth()->user()->hasRole('Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($tarea);
    }

    public function update(Request $request, string $id)
    {
        $tarea = Tarea::findOrFail($id);

        // Solo el dueño o un admin puede actualizar
        if ($tarea->user_id !== Auth::id() && !auth()->user()->hasRole('Admin')) {
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

    public function destroy(string $id)
    {
        $tarea = Tarea::findOrFail($id);

        // Solo el dueño o un admin puede eliminar
        if ($tarea->user_id !== Auth::id() && !auth()->user()->hasRole('Admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tarea->delete();

        return response()->json(['mensaje' => 'Tarea eliminada'], 204);
    }
}
