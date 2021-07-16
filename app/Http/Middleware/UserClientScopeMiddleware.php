<?php

namespace App\Http\Middleware;

use Closure;
use App\Client;

class UserClientScopeMiddleware
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
        $client = Client::fcid($request->client_id);
        if (is_null($client)) {
            abort(404);
        }
        return in_array($client->office_id,auth()->user()->scopesID()) ? $next($request) : abort(403);
    }
}
