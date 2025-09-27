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
}