<?php

namespace App\Http\Controllers;

use App\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    //

    public function getList(){
        $list = Fee::select(['id','name'])->where('disabled',false)->orderBy('name','asc')->get();
        $filtered[] = array('level'=>'Fees','data'=>$list);
        return $filtered;
    }
}
