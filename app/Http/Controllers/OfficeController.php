<?php

namespace App\Http\Controllers;

use App\Office;
use App\Rules\OfficeID;
use App\Rules\OfficeLevel;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class OfficeController extends Controller
{
    public function createOffice(Request $request){

        
        $this->validator($request->all())->validate();
        
            Office::create(
                [
                    'code' => $request->level_code,
                    'name' => $request->name,
                    'level' => $request->level,
                    'level_in_number' => $request->level_in_number,
                    'parent_id' => $request->office_id
                ]
            ); 
            return response()->json(['msg'=>'Office succesfully created'],200);
           
        //    return response()->json(['errors'=>$e->getErrors()],500);
        
    }

    public function validator(array $data, $for_update=false, $id=null){
    	   
        if ($for_update) {
            return Validator::make(
                $data,
                [
                    'office_id'=>['required', new OfficeID],
                    'code' => 'required|unique:offices,code,'.$id,
                    'name' => 'required|unique:offices,name,'.$id,
                    'level' => ['required', new OfficeLevel]
                ]
            );
        }

        return Validator::make(
    		$data,
    		[
    			'office_id'=>['required', new OfficeID],
    			'code' => 'required|unique:offices,code',
                'name' => 'required|unique:offices,name',
    			'level' => ['required', new OfficeLevel]
    		]
    	);
    }

    public function generateCode(Request $request)
    {
    	$code = $request->code;     
    	if ($request->level == "account_officer") {
    		$office = Office::find($request->office_id);
    		$children = $office->getAllChildren();
    		$code = $office->code;
    		$list = collect($children)->filter(function($item){
    			return $item->level == "account_officer";
    		});
    		if ($list->count() > 0) {
    			return $request->code.pad($list->count()+1, 2);
    		}else{
    			return $request->code. pad(1, 2);
    		}
    	}
    	return $code;
	}
	public function createLevel($level){
			$list_level = Office::getParentOfLevel($level);
			return $list_level=="" ? abort(404): view('pages.create-office',compact(['level','list_level']));
			
	}

    public function viewOffice($level){
        $office = Office::where('level', $level)->first();
        $list_level = Office::getParentOfLevel($level);
        return view('pages.office-list', compact(['level','list_level']));
    }

    public function getOfficeList(Request $request, $level){
        $officeList = Office::like($level, $request->search)->paginate(15);
        return response()->json($officeList);
    }

    public function editOffice($id){
        $office = Office::with('parent')->find($id);
        if (empty($office)) {
            return response()->json(['error' => "Office does not exist"],404);
        }
        return response()->json($office);
    }

    public function updateOffice(Request $request, $id){
        
        $this->validator($request->all(),true,$id)->validate();
        $office = Office::find($id);
        try{
        $office->update(
            [
               'name' => $request->name,
                'code' => $this->generateCode($request),
                'parent_id' => $request->office_id,
                'level_in_number' => $request->level_in_number,
                'level' => $request->level
            ]
        );
        return response()->json(['msg'=>'Office succesfully updated'],200);
        }catch(ValidationException $e)
        {   
            return response()->json(['errors'=>$e->getErrors()],500);
        }
        
    }
}
