<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:view_roles', ['only' => ['index','getRoles']]);
         $this->middleware('permission:create_role', ['only' => ['create','store']]);
         $this->middleware('permission:edit_role', ['only' => ['edit','update']]);
    }

    public function create(){
    	return view('pages.roles_permission.create-roles');
    }

    public function index(){
      return view('pages.roles_permission.roles-list');
    }

    public function getRoles(){
      
      if (request()->paginate == true) {
        $roles = Role::paginate(25);
      }else{
        $roles = Role::all();
      }
    	return response()->json($roles);
    }


    public function edit(Role $role){
    	$permissions =[];
    	foreach ($role->getAllPermissions() as $key => $value) {
    		array_push($permissions, $value->name);
    	}
    	return response()->json($role);
    }

    public function update(Request $request, Role $role){
        
      $this->validateRequest();
      $role->update(['name' => $request->name]);
      $permission_ids = [];
      foreach ($request->permission_ids as $permission) {
        
        array_push($permission_ids, $permission['key']);
      }
      $role->syncPermissions($permission_ids);
      return response()->json(['message' => 'Success']);
       
    }

    public function store(Request $request){	

   		  $this->validateRequest();
   			$role = Role::create(['name' => $request->name]);

   			$role->syncPermissions($request->permission_ids);

   			return response()->json(['message' => 'Success']);
   		
    }

  public function validateRequest(){
      try {
        request()->validate(
          ['name' => 'required']
        );
      } catch (Exception $e) {
        return response()->json($e);
      }
    }
}
