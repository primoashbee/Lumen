<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Cluster extends Model
{

    protected $fillables = ['office_id', 'client_id', 'name', 'officer_id', 'notes'];

    protected $searchables = [
        'name',
    ];

    public function officer(){
        return $this->belongsTo(User::class);
    }

    public function office(){
        return $this->belongsTo(Office::class);
    }

    public function getClusterBranch(){
        return $this->office->getTopOffice('branch')->name;
    }

    public static function like($user, $query){
        $me = new static;
        $searchables = $me->searchables;
        $user = User::find($user);
        $scopes = collect($user->scopes());
        
        $scopes = $scopes->filter(function ($item) {
            return $item->level == 'cluster';
        });
        
        $clusters = Office::with('parent.parent')->whereIn('id',$scopes->pluck('id'))->get();
        
        if(count($clusters)>0){
            if($query!=null){
                $clusters = Office::with('parent.parent')->whereIn('id',$scopes->pluck('id'))->where(function(Builder $dbQuery) use($searchables, $query){
                    foreach($searchables as $item){  
                        $dbQuery->where($item,'LIKE','%'.$query.'%');
                    }
                });
                return $clusters;
            }
            $clusters = Office::with('parent.parent')->whereIn('id',$scopes->pluck('id'));
            
            return $clusters;
        }
        
        return $clusters;
    }
}
