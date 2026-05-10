<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        if (! in_array($userRole, $roles, true)) {
            abort(403, $this->buildMessage($roles));
        }

        return $next($request);
    }

    /**
     * Build the Indonesian "tidak bisa masuk sini" message that names the
     * roles that ARE allowed, e.g. "...sebagai admin atau chef".
     */
    private function buildMessage(array $roles): string
    {
        $names = array_map(fn ($r) => strtolower($r), $roles);

        if (count($names) === 1) {
            $list = $names[0];
        } elseif (count($names) === 2) {
            $list = "{$names[0]} atau {$names[1]}";
        } else {
            $last = array_pop($names);
            $list = implode(', ', $names) . ", atau {$last}";
        }

        return "Tidak bisa masuk sini ya... harus login dulu sebagai {$list}";
    }
}
