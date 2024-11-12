<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // dd("ikik");

        $token = $request->header('Authorization'); // Get the token from the Authorization header

        if (!$token || !User ::where('token', $token)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
// dd("JIJI");
        return $next($request);
    }
}
