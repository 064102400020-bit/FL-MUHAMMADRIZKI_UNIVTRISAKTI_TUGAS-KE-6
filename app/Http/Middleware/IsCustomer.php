<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    /**
     * Handle an incoming request.
     * Memastikan request berasal dari user yang sudah terautentikasi dan memiliki role 'customer'.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah pengguna sudah terautentikasi (memiliki token valid)
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        // 2. Cek apakah pengguna memiliki role 'customer'
        if ($request->user()->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh customer.',
            ], 403);
        }

        return $next($request);
    }
}
