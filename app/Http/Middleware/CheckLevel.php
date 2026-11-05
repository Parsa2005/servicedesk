<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLevel
{
    public function handle(Request $request, Closure $next, ...$levels): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->level, $levels)) {
            abort(403, 'شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
        }

        return $next($request);
    }
}