<?php

namespace App\Http\Middleware;

use Closure;

class UserHasAccessToOffice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(is_null(session('accessible_office_ids'))){
            auth()->logout();
            return redirect('/');
        }
        $accessible_office_ids = session('accessible_office_ids');
        if(!in_array($request->office_id, $accessible_office_ids)){
            abort(403);
        }
        return $next($request);
    }
}
