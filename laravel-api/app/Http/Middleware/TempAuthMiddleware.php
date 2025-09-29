<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TempAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Modo de prueba simplificado
        $fakeUser = (object) [
            'id' => 1,
            'nombre' => 'Usuario de prueba',
            'email' => 'admin@empresa1.com',
            'rol' => 'admin'
        ];
        
        $request->attributes->set('auth_user', $fakeUser);
        $request->attributes->set('auth_empresa', 'empresa1');
        
        return $next($request);
    }
}
