<?php

namespace App\Http\Controllers;

use App\Models\Libro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LibroController extends Controller
{
    public function index(): JsonResponse
    {
        $libros = Libro::all();

        return response()->json([
            'data' => $libros,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $libro = Libro::find($id);

        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        return response()->json([
            'data' => $libro,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $libro = Libro::create($validator->validated());

        return response()->json([
            'message' => 'Libro creado correctamente',
            'data' => $libro,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $libro = Libro::find($id);

        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $libro->update($validator->validated());

        return response()->json([
            'message' => 'Libro actualizado correctamente',
            'data' => $libro,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $libro = Libro::find($id);

        if (!$libro) {
            return response()->json([
                'message' => 'Libro no encontrado',
            ], 404);
        }

        $libro->delete();

        return response()->json([
            'message' => 'Libro eliminado correctamente',
        ]);
    }

    private function rules(): array
    {
        return [
            'titulo' => ['required', 'string'],
            'autor' => ['required', 'string'],
            'anio_publicacion' => ['required', 'integer'],
            'genero' => ['required', 'string'],
            'disponible' => ['boolean'],
        ];
    }
}
