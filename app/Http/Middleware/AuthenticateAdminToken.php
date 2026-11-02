<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $diharapkan = (string) config('services.hermes.token');
        $diberikan = $request->bearerToken();

        if ($diharapkan === '' || $diberikan === null || ! hash_equals($diharapkan, $diberikan)) {
            return response()->json(['error' => 'Token admin tidak valid.'], 401);
        }

        return $next($request);
    }
}
