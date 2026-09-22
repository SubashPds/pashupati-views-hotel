<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();
        return $this->secure($next($request), $request);
    }

    public function secure(Response $response, Request $request): Response
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->remove('X-Powered-By');
        $policy = "base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'";
        if (! (app()->environment('local') && Vite::isRunningHot())) {
            $nonce = Vite::cspNonce();
            $policy .= "; default-src 'self'; script-src 'self' 'nonce-{$nonce}' https://cdn.ckeditor.com; script-src-attr 'none'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.ckeditor.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: blob: https: http:; media-src 'self' blob: https: http:; connect-src 'self' https://cdn.ckeditor.com; frame-src https://www.google.com https://maps.google.com";
        }
        $response->headers->set('Content-Security-Policy', $policy);
        if ($request->isSecure()) $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        if ($request->is('admin', 'admin/*', 'login')) $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
