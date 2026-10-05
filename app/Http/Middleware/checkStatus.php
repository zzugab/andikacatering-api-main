<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$status): Response
    {
        $user = $request->user();
        $is_status = (bool)$user->is_active;
        if (! $user || ! in_array($is_status, $status)) {
            return response()->json([
                'message' => 'Silahkan Hubungi Admin Untuk Aktivasi',
            ], 403);
        }

        return $next($request);
    }
}
