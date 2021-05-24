<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    public function __construct(){
        $this->middleware('permission:view_permssions', ['only' => ['index','getPermissions']]);
        $this->middleware('permission:create_permssions', ['only' => ['store']]);
    }

    public function index(){
    	return view('pages.roles_permission.permissions-list');
    }

    public function getPermissions(){
        
        if (request()->paginate == true){
            $permissions = Permission::paginate(25);
          }else{
            $permissions = Permission::all();
          }
        return response()->json($permissions);
    }

    public function store(Request $request){
    	$request->validate(['permission_name' => 'required']);
    	Permission::create(['name' => $request->permission_name]);
    	return response()->json(['message' => 'Success']);
    }
}
