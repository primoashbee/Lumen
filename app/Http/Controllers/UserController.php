<?php

namespace App\Http\Controllers;

use App\User;
use App\OfficeUser;
use App\Office;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{   
    public function __construct()
    {
        $this->middleware('permission:view_user', ['only' => ['users']]);
         $this->middleware('permission:create_user', ['only' => ['create','storeUser']]);
         $this->middleware('permission:edit_user', ['only' => ['edit','update']]);
    }

    
    public function users(){
        return view('pages.users.users-list');
    }

    public function getUsers(){
        $users = User::with('office:id,name','roles:id,name')->paginate(25);

        if (request()->has('search') || request()->has('office_id')) {
            $users = User::searchOfficeUsers(request()->search, request()->office_id)->paginate(25);
        }
        
        return response()->json($users);
    }

    public function create(){
        return view('pages.users.create-user');
    }

    public function storeUser(UserRequest $request){
        $user = User::create($request->all());
        $user->syncRoles($request->role_ids);

        $office_ids = [];
        foreach ($request->office_ids as $key => $value) {
            array_push($office_ids, $value['key']);
        }
        
        $user->office()->attach($office_ids);
        return response()->json(['message' => 'Success!!']);
    }

    public function edit(User $user){
        $user->load('office','roles');
        return view('pages.users.update-user', compact('user'));
    }

    public function update(UserRequest $request, User $user){
        $office_ids = [];
        foreach ($request->office_ids as $key => $value) {
            array_push($office_ids, $value['key']);
        }
        $user->office()->detach();
        $user->office()->attach($office_ids);
        
        $user->update($request->except('password'));
        $user->syncRoles($request->role_ids);

        return response()->json(["Message" => "Success!"]);
    }

    public function authStructure(){
        return auth()->user()->scopes();
    }

    public function branches(Request $request){
        return auth()->user()->scopesBranch($request->level);
    }

    public function get(Request $request, User $user){
        return $user;
    }

    public function changepass(Request $request, User $user){
        try {
            $x= $request->validate(
                [
                    'password' => 'required|confirmed|min:8',
                ]
            );
            $user = User::find($request->id);
            $user->update(
                ['password' => Hash::make($request->password)]
            );

            return response()->json(['Message' => 'Success!']);

        } catch (Exception $e) {
            return response()->json($e);
        }
    }

    public function createUser(){
        return view('pages.users.create-user');
    }

}
