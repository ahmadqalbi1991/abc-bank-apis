<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppVerifyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $appKey = $request->header('app_key');
            $envKey = env('APP_KEY', config('app.key'));
            if (!$appKey) {
                return response()->json(['status' => false, 'message' => 'App key is missing'], 403);
            }

            $envKey = explode(':', $envKey)[1];
            if ($appKey === $envKey) {
                return $next($request);
            } else {
                return response()->json(['status' => false, 'message' => 'Something went wrong'], 403);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => 'Something went wrong'], 403);
            Log::error($exception);
        }
    }
}
