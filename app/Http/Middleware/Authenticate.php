<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Token Sudah Kardaluasa',
                'data' => null
            ], 401);
        }

        return $next($request);
    }
}
