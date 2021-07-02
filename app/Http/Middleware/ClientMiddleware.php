<?php

namespace App\Http\Middleware;

use Closure;
use App\Client;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Client $client,$request, Closure $next)
    {


        if (auth()->user()->office->id) {
            # code...
        }

        return $next($request);
    }
}
