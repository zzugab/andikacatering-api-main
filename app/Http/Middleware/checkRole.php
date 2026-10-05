<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RolesModel;

class checkRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    private $role;
    public function __construct()
    {
        $this->role = new RolesModel();
    }

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $checkRole = $this->role->getById($user->role_id)['data'];
        if (! $user || ! in_array($checkRole['name'], $roles)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        return $next($request);
    }
}
