<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Load the user's roles
        $user->load('usuariosRoles.role');

        // Check if the user has an admin role
        $isAdmin = $user->usuariosRoles->contains(function ($usuarioRol) {
            return strtolower($usuarioRol->role->nombre) === 'admin';
        });

        if (!$isAdmin) {
            Log::info('User does not have admin role, redirecting to dashboard.', ['user_id' => $user->id]);
            return redirect('/dashboard');
        }

        Log::info('User has admin role.', ['user_id' => $user->id]);

        return $next($request);
    }
}
