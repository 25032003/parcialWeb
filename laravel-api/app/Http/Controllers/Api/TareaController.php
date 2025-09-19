<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TareaController extends Controller
{
    /**
     * Listado de tareas con usuario asignado
     */
    public function index()
    {
        $tareas = Tarea::with(['usuario:id,nombre'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($tareas);
    }

    /**
     * Crear una nueva tarea asignada a un usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        if (!isset($validated['estado'])) {
            $validated['estado'] = 'pendiente';
        }

        $tarea = Tarea::create($validated);

        return response()->json([
            'message' => 'Tarea creada correctamente',
            'data' => $tarea->load('usuario:id,nombre')
        ], 201);
    }

    /**
     * Exportar CSV de tareas pendientes (sin librerías externas)
     */
    public function exportPendientes()
    {
        $tareas = Tarea::with(['usuario:id,nombre'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->get();

        $fileName = 'tareas_pendientes.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($tareas) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel/Windows
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Indicar a Excel el separador a usar (importante en configuraciones regionales)
            fwrite($out, "sep=;\r\n");

            // Encabezados (usando ';' como delimitador)
            fputcsv($out, ['ID', 'Titulo', 'Usuario', 'Estado', 'Fecha Vencimiento', 'Creado'], ';');

            foreach ($tareas as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->titulo,
                    optional($t->usuario)->nombre,
                    $t->estado,
                    optional($t->fecha_vencimiento)->format('Y-m-d'),
                    optional($t->created_at)->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
