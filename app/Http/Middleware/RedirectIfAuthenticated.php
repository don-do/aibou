<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // ログイン状態
        if (Auth::guard($guard)->check()) {
            // 非ログイン状態でのみアクセスできる機能にリクエストを送信した場合
            // ログインユーザー返却APIにリダイレクト
            return redirect()->route('user');
        }

        return $next($request);
    }
}
