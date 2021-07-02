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
        $client_office_id = null;
        if ($request->client_id) {
            $client_office_id = Client::where('client_id',$request->client_id)->first()->office_id;
        }

        if ($request->client) {
            $client_office_id = $request->client->office_id;
        }   
        if (!in_array($client_office_id, $request->session()->get('scopes')))
        {
            abort(403);
        }
        return $next($request);
       
        
        
    }
}
