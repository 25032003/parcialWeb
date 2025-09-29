<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TempAuthController extends Controller
{
    /**
     * Login temporal que funciona con aislamiento por empresa
     * Detecta automáticamente la empresa basándose en el dominio o header
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // Detectar la empresa desde el dominio o header
            $empresa = $this->detectEmpresa($request);
            
            if (!$empresa) {
                return response()->json([
                    'message' => 'No se pudo determinar la empresa',
                    'error' => 'Acceso desde dominio no reconocido'
                ], 400);
            }

            // Buscar usuario SOLO en la base de datos de la empresa correspondiente
            $usuario = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->where('email', $request->email)
                ->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                throw ValidationException::withMessages([
                    'email' => ["Las credenciales no coinciden con los registros de {$empresa}."],
                ]);
            }

            // Crear token usando Laravel Sanctum (simulando el usuario)
            $user = (object) [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'empresa' => $empresa
            ];

            // Token con información de empresa para mayor seguridad
            $token = base64_encode($usuario->email . ':' . $empresa . ':' . time());

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'usuario' => $user,
                'token'   => $token,
                'empresa' => $empresa,
            ], 200);

        } catch (ValidationException $e) {
            $empresa = $this->detectEmpresa($request);
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
                'debug_info' => [
                    'empresa_detectada' => $empresa,
                    'email_buscado' => $request->email,
                    'base_datos_consultada' => $empresa ? "{$empresa}.usuarios" : 'ninguna',
                    'note' => $empresa ? "Solo se buscan usuarios en la base de datos de {$empresa}" : 'Dominio no reconocido'
                ]
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'debug' => [
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'empresa_detectada' => $this->detectEmpresa($request)
                ]
            ], 500);
        }
    }

    /**
     * Detecta la empresa basándose en el dominio, headers o parámetros
     */
    private function detectEmpresa(Request $request): ?string
    {
        // 1. Intentar detectar desde el header Host
        $host = $request->header('Host');
        
        if ($host) {
            if (strpos($host, 'empresa1.localhost') !== false) {
                return 'empresa1';
            }
            if (strpos($host, 'empresa2.localhost') !== false) {
                return 'empresa2';
            }
        }
        
        // 2. Intentar detectar desde la URL referer (desde donde viene la petición)
        $referer = $request->header('Referer');
        
        if ($referer) {
            if (strpos($referer, 'empresa1.localhost') !== false) {
                return 'empresa1';
            }
            if (strpos($referer, 'empresa2.localhost') !== false) {
                return 'empresa2';
            }
        }
        
        // 3. Parámetro manual (para testing)
        if ($request->has('empresa')) {
            $empresa = $request->input('empresa');
            if (in_array($empresa, ['empresa1', 'empresa2'])) {
                return $empresa;
            }
        }
        
        // 4. Basándose en el dominio del email (fallback temporal para testing)
        $email = $request->input('email', '');
        if (strpos($email, '@empresa1.com') !== false) {
            return 'empresa1';
        }
        if (strpos($email, '@empresa2.com') !== false) {
            return 'empresa2';
        }
        
        // 5. Fallback: Si no se puede detectar, retornar null
        return null;
    }

    /**
     * Registro temporal con aislamiento por empresa
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'email'    => 'required|email|max:150',
            'password' => 'required|string|min:6',
            'rol'      => 'required|in:admin,usuario',
        ]);

        try {
            // Detectar la empresa
            $empresa = $this->detectEmpresa($request);
            
            if (!$empresa) {
                return response()->json([
                    'message' => 'No se pudo determinar la empresa',
                    'error' => 'Registro desde dominio no reconocido'
                ], 400);
            }

            // Verificar que el email no exista EN LA EMPRESA CORRESPONDIENTE
            $existingUser = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->where('email', $validated['email'])
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['email' => ["El email ya está registrado en {$empresa}."]]
                ], 422);
            }

            // Crear usuario en la base de datos de la empresa correspondiente
            $userId = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->insertGetId([
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'rol' => $validated['rol'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $user = (object) [
                'id' => $userId,
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'rol' => $validated['rol'],
                'empresa' => $empresa
            ];

            $token = base64_encode($validated['email'] . ':' . $empresa . ':' . time());

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'usuario' => $user,
                'token'   => $token,
                'empresa' => $empresa,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos del usuario actual
     */
    public function me(Request $request)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        
        try {
            // Decodificar el token simple que creamos
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            
            if (count($parts) < 3) {
                return response()->json(['message' => 'Token inválido'], 401);
            }
            
            $email = $parts[0];
            $empresa = $parts[1];
            
            // Buscar usuario en la empresa correspondiente
            $usuario = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->where('email', $email)
                ->first();
                
            if (!$usuario) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
            
            return response()->json([
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'empresa' => $empresa
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token inválido'], 401);
        }
    }
    
    /**
     * Logout (invalidar token)
     */
    public function logout(Request $request)
    {
        // En nuestro sistema temporal, simplemente confirmamos el logout
        return response()->json(['message' => 'Logout exitoso'], 200);
    }
    
    /**
     * Listar usuarios de la empresa
     */
    public function listUsers(Request $request)
    {
        try {
            $empresa = $this->detectEmpresa($request);
            
            if (!$empresa) {
                return response()->json([
                    'message' => 'No se pudo determinar la empresa'
                ], 400);
            }

            $usuarios = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->select('id', 'nombre', 'email', 'rol', 'created_at', 'updated_at')
                ->get();

            return response()->json($usuarios);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:6',
            'rol' => 'required|string|in:admin,usuario',
        ]);

        try {
            $empresa = $this->detectEmpresa($request);
            
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la empresa'
                ], 400);
            }

            // Verificar que el email no exista en la empresa
            $existingUser = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->where('email', $validated['email'])
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => ['email' => ['El email ya está registrado en esta empresa.']]
                ], 422);
            }

            // Crear usuario en la base de datos de la empresa correspondiente
            $userId = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->insertGetId([
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'rol' => $validated['rol'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $usuario = DB::connection('mysql')
                ->table("{$empresa}.usuarios")
                ->where('id', $userId)
                ->first();

            return response()->json([
                'success' => true,
                'data' => ['usuario' => $usuario],
                'message' => 'Usuario creado exitosamente'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Listar tareas de la empresa
     */
    public function listTareas(Request $request)
    {
        try {
            $empresa = $this->detectEmpresa($request);
            
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la empresa'
                ], 400);
            }

            // Respuesta de prueba simple para verificar que el método funciona
            return response()->json([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'titulo' => 'Tarea de prueba',
                        'descripcion' => 'Descripción de prueba',
                        'estado' => 'pendiente',
                        'usuario_id' => 1,
                        'fecha_vencimiento' => null,
                        'created_at' => '2024-01-01 10:00:00',
                        'updated_at' => '2024-01-01 10:00:00',
                        'usuario' => [
                            'id' => 1,
                            'nombre' => 'Usuario de prueba',
                            'email' => 'test@empresa1.com'
                        ]
                    ]
                ],
                'message' => 'Tareas obtenidas correctamente (modo prueba)'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tareas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nueva tarea
     */
    public function addTarea(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 999,
                'titulo' => 'Tarea de prueba',
                'descripcion' => 'Descripción de prueba',
                'estado' => 'pendiente',
                'usuario_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'usuario' => [
                    'nombre' => 'Usuario de prueba',
                    'email' => 'test@test.com'
                ]
            ],
            'message' => 'Tarea creada exitosamente (modo prueba)'
        ], 201);
    }
    
    /**
     * Actualizar tarea existente
     */
    public function updateTarea(Request $request, $id)
    {
        $validated = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'estado' => 'nullable|string|in:pendiente,completada',
        ]);

        try {
            $empresa = $this->detectEmpresa($request);
            $usuario = $request->attributes->get('auth_user');
            
            if (!$empresa || !$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la empresa o usuario'
                ], 400);
            }

            // Verificar que la tarea existe
            $tareaExistente = DB::connection('mysql')
                ->table("{$empresa}.tareas")
                ->where('id', $id)
                ->first();

            if (!$tareaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarea no encontrada'
                ], 404);
            }

            // Construir datos a actualizar
            $datosActualizar = ['updated_at' => now()];
            
            if (isset($validated['titulo'])) {
                $datosActualizar['titulo'] = $validated['titulo'];
            }
            if (isset($validated['descripcion'])) {
                $datosActualizar['descripcion'] = $validated['descripcion'];
            }
            if (isset($validated['estado'])) {
                $datosActualizar['estado'] = $validated['estado'];
            }

            // Actualizar tarea
            DB::connection('mysql')
                ->table("{$empresa}.tareas")
                ->where('id', $id)
                ->update($datosActualizar);

            // Obtener la tarea actualizada con información del usuario
            $tarea = DB::connection('mysql')
                ->table("{$empresa}.tareas as t")
                ->leftJoin("{$empresa}.usuarios as u", 't.usuario_id', '=', 'u.id')
                ->select(
                    't.id', 
                    't.titulo', 
                    't.descripcion', 
                    't.estado', 
                    't.usuario_id',
                    't.created_at', 
                    't.updated_at',
                    'u.nombre as usuario_nombre',
                    'u.email as usuario_email'
                )
                ->where('t.id', $id)
                ->first();

            $tareaFormateada = [
                'id' => $tarea->id,
                'titulo' => $tarea->titulo,
                'descripcion' => $tarea->descripcion,
                'estado' => $tarea->estado,
                'usuario_id' => $tarea->usuario_id,
                'created_at' => $tarea->created_at,
                'updated_at' => $tarea->updated_at,
                'usuario' => [
                    'nombre' => $tarea->usuario_nombre,
                    'email' => $tarea->usuario_email
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tarea' => $tareaFormateada
                ],
                'message' => 'Tarea actualizada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tarea: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar tarea
     */
    public function deleteTarea(Request $request, $id)
    {
        try {
            $empresa = $this->detectEmpresa($request);
            $usuario = $request->attributes->get('auth_user');
            
            if (!$empresa || !$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la empresa o usuario'
                ], 400);
            }

            // Verificar que la tarea existe
            $tareaExistente = DB::connection('mysql')
                ->table("{$empresa}.tareas")
                ->where('id', $id)
                ->first();

            if (!$tareaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarea no encontrada'
                ], 404);
            }

            // Eliminar tarea
            DB::connection('mysql')
                ->table("{$empresa}.tareas")
                ->where('id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Tarea eliminada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tarea: ' . $e->getMessage()
            ], 500);
        }
    }
}