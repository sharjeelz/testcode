<?php namespace App\Http\Middleware;

use Closure;
//use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\Request;
use Redirect;
class SuperAdminAuth  {

    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->isSuperAdmin) {
            return $next($request);
        }
    
        abort(404);
    }

}
