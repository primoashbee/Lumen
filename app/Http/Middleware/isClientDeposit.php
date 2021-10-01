<?php

namespace App\Http\Middleware;

use App\DepositAccount;
use Closure;

class isClientDeposit
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
        return DepositAccount::findOrfail($request->deposit_account_id)->client()->pluck('client_id')->first() == $request->client_id ? $next($request) : abort(403);
    }
}
