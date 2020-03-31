<?php

namespace App\Http\Controllers\API;

use App\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StructureController extends Controller
{
    public function index(){
        
        // $office = new Office();
        // $office = $office->branches();
        // $regions = $office->allRegion();
        // $areas = $office->allArea();
        // $branches = $office->allBranch();
        // $data = compact('office','regions','areas','branches');
        // $office = new Office();
        // $office->get()
        return Office::get(['id','level','name']);
    }

    public function auth(){
        dd(auth('api')->user());
        return auth()->user()->scopes();
    }
    public function branches(){
        dd(auth('api')->user());
        return auth()->user()->scopes();
    }
}
