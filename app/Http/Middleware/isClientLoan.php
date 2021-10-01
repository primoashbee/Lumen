<?php

namespace App\Http\Middleware;

use Closure;
use App\LoanAccount;

class isClientLoan
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
        return LoanAccount::findOrfail($request->loan_id)->client()->pluck('client_id')->first() == $request->client_id ? $next($request) : abort(403);
    }
}
