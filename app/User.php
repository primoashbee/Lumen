<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname','lastname','middlename','gender','birthday','notes','email', 'password','created_by',
   ]; 

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function name(){
        return $this->firstname.' '.$this->lastname;
    }
    
    public function office(){
        return $this->belongsToMany(Office::class)->orderBy('office_id');
    }


    public function scopes(){
       
        $offices = $this->office;
    
        $scopes = [];
        foreach($offices as $office){    
            array_push($scopes,$office);
            $scopes = array_merge($scopes, $office->getChild());
        }

        return $scopes;
    }

    public function scopesBranch(){

        $collection = collect($this->scopes());
        $branches = [];
        $clusters = [];
        $officers = [];

        
        $branches = $collection->filter(function($item){
            return $item->level=="branch";
        })->values();
        
        $branches = $branches->map(function($item){
            $branch['id'] = $item->id;
            $branch['name'] = $item->name;
            return $branch;
        });
        
        $clusters = $collection->filter(function($item){
            return $item->level=="cluster";
        })->values();

        $clusters = $clusters->map(function($item){
            $cluster['id'] = $item->id;
            $cluster['name'] = $item->name;
            return $cluster;
        });
        
        $officers = $collection->filter(function($item){
            return $item->level=="officer";
        })->values();
        

        $officers = $officers->map(function($item){
            $officer['id'] = $item->id;
            $officer['name'] = $item->name;
            return $officer;
        });
        
        $filtered = [
            ['level' => 'Branches', 'data' => collect($branches)->sortBy('name')->unique()->values()], 
            ['level' => 'Clusters', 'data' => collect($clusters)->sortBy('name')->unique()->values()], 
            ['level' => 'Officers', 'data' => collect($officers)->sortBy('name')->unique()->values()]
        ];

        return $filtered;

    }
    public function scopesID(){
       
        $offices = $this->office;

        $scopes = [];
        foreach($offices as $office){
            array_push($scopes,$office->id);
            $scopes = array_merge($scopes, $office->getChildIDS());
        }

        return $scopes;
    }


}
