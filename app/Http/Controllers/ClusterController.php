<?php

namespace App\Http\Controllers;

use App\Office;
use App\User;
use App\Cluster;
use Illuminate\Http\Request;

class ClusterController extends Controller
{

    public function __construct(){

        $this->middleware('permission:view_cluster', ['only' => ['index','getClustersList']]);
        $this->middleware('permission:create_cluster', ['only' => ['create']]);

    }

    public function index(){
        return view('pages.clusters.index');
    }
    
    public function getClustersList(Request $request){
        
        $cluster = Cluster::like(auth()->user()->id,$request->search)->paginate(15);
        
        return response()->json($cluster);

    }

    public function create(){
        return view('pages.clusters.create-cluster');
    }

}
