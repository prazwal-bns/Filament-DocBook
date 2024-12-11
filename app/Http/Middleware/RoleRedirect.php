<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $role
     */

    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        // if user is admin and he/she is trying to access patient or doctor's dashboard
        if ($user->role === 'admin' && in_array($role, ['patient', 'doctor'])) {
            return $this->handleResponse($request, 'admin.dashboard');
        }

        // Redirect patient trying to access doctor dashboard
        if ($role === 'doctor' && $user->role == 'patient') {
            // return redirect()->route('patient.dashboard');
            return $this->handleResponse($request, 'patient.dashboard');
        }

        // Redirect doctor trying to access patient dashboard
        if ($role === 'patient' && $user->role == 'doctor') {
            // return redirect()->route('doctor.dashboard');
            return $this->handleResponse($request, 'doctor.dashboard');
        }

        return $next($request);
    }

     /**
     * Handle the response for both web and API.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string $redirectRoute
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleResponse(Request $request, string $redirectRoute): Response
    {

        if ($request->is('api/*') || $request->expectsJson()) {
            // Return response in JSON format for API Requests
            return response()->json([
                'message' => 'Forbidden: You are not allowed to access this resource',
                'redirect' => route($redirectRoute)
            ],403);
        }

        return redirect()->route($redirectRoute);
    }
}
