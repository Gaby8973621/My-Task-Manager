<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    public function index(Request $request) // <- Se inyecta $request correctamente
    {
        $user = auth()->user();

        // Construimos la query base
        $query = $user->tareas();

        // Filtro por estado de completada
        if ($request->has('completada')) {
            $query->where('completada', filter_var($request->completada, FILTER_VALIDATE_BOOLEAN));
        }

        // Filtro por búsqueda en el título
        if ($request->filled('buscar')) {
            $query->where('titulo', 'like', '%' . $request->buscar . '%');
        }

        // Orden por fecha
        $orden = $request->get('orden', 'desc'); // 'asc' o 'desc'
        $query->orderBy('created_at', $orden);

        // Paginación: 10 por página por defecto
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

        if ($tarea->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($tarea);
    }

    public function update(Request $request, string $id)
    {
        $tarea = Tarea::findOrFail($id);

        if ($tarea->user_id !== Auth::id()) {
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

        if ($tarea->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $tarea->delete();

        return response()->json(['mensaje' => 'Tarea eliminada'], 204);
    }
}
