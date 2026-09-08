<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (! $user->isActive()) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account is currently inactive or suspended.']);
        }

        if (! $user->hasPermissionTo($permission)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized access to action: ' . $permission);
        }

        return $next($request);
    }
}
