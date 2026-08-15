<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicDemoSafety
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled')) {
            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();

        if ($this->isReadOnlyRoute($routeName)
            && (! $request->isMethodSafe() || Str::endsWith($routeName, ['.create', '.edit']))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.demo_read_only_action')], 403);
            }

            return redirect()->route('dashboard')->with('error', __('messages.demo_read_only_action'));
        }

        if (! $request->isMethodSafe()) {
            $this->guardWriteRate($request);
        }

        return $next($request);
    }

    private function isReadOnlyRoute(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }

        foreach (config('demo.read_only_routes', []) as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
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
