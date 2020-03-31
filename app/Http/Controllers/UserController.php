<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function users(){
        $users = User::all();
        return view('pages.users.users',compact('users'));
    }

    public function authStructure(){
        return auth()->user()->scopes();
    }

    public function branches(){     
        return auth()->user()->scopesBranch();
    }
}
