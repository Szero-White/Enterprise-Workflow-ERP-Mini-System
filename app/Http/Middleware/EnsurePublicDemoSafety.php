<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicDemoSafety
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled')) {
            return $next($request);
        }

        if (! $request->isMethodSafe()) {
            $this->guardWriteRate($request);
        }

        return $next($request);
    }

    private function guardWriteRate(Request $request): void
    {
        $actor = $request->user()?->id ?? 'guest';
        $identity = sprintf('%s|%s', $actor, $request->ip());

        $minuteLimit = max(1, (int) config('demo.max_writes_per_minute', 15));
        $minuteKey = "public-demo-write:minute:{$identity}";

        $hourLimit = max(1, (int) config('demo.max_writes_per_hour', 60));
        $hourKey = "public-demo-write:hour:{$actor}";

        if (RateLimiter::tooManyAttempts($minuteKey, $minuteLimit)
            || RateLimiter::tooManyAttempts($hourKey, $hourLimit)) {
            abort(429, __('messages.demo_write_rate_limited'));
        }

        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($hourKey, 3600);
    }
}
