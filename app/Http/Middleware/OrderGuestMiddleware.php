<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderGuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Order-Token');

        if (!$token) {
            return response()->json(['message' => 'Missing Order Token'], 403);
        }

        // Get command ID from route
        $commandeId = $request->route('commande');
        // Handle if route param is model object or ID
        if ($commandeId instanceof \App\Models\Commande) {
            $commandeId = $commandeId->id;
        }

        if (!$commandeId) {
            // If checking a route where command ID isnt in path (rare but possible), skip or strictly fail?
            // For now, fail if we rely on this middleware for command-specific actions
             return response()->json(['message' => 'Invalid Context'], 400); 
        }

        $session = \App\Models\OrderSession::where('token', $token)
                    ->where('commande_id', $commandeId)
                    ->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid or Expired Order Session'], 403);
        }

        return $next($request);
    }
}
