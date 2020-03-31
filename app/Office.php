<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    // protected $with = ['parent'];

    public function parent(){
        return $this->belongsTo(static::class, 'parent_id');
    }
    
    public function children(){
        return $this->hasMany(static::class, 'parent_id');
    }   

    public function user(){
        return $this->belongsToMany(User::class);
    }

    public function clients(){
        return $this->hasMany(Client::class);
    }

    public function staffs(){
        return $this->belongsToMany(User::class);
    }


    public function getChildIDS(){
        $children = $this->children;
        
        $count = $this->children->count();
        $ids = [];
        //if ($count>0) {
            foreach ($children as $child) {
                array_push($ids,$child->id);
                $ids = array_merge($ids, $child->getChildIDS());
            }
        //}

        return $ids;
    }

    public function getChild(){
        $children = $this->children;
        
        // $count = $this->children->count();
        $result = [];
        //if ($count>0) {
            foreach ($children as $child) {
                array_push($result,$child);
                $result = array_merge($result, $child->getChild());
            }
        //}

        return $result;
    }
    public function getAllChildren(){
        $children = $this->children;
        // $count = $this->children->count();
        $ids = [];
        //if ($count>0) {
            foreach ($children as $child) {
                array_push($ids,$child);
                $ids = array_merge($ids, $child->getAllChildren());
            }
        //}
        return $ids;
    }

    public function getParent(){
        return $this->parent;
    }
    public function getBranch(){
        $parents = [];
        echo $this->name;
        if($this->level=="branch"){
            return 'nice';
        }

        array_push($parents, $this);
        $this->getBranch();
        return $parents;
    
                
    }

    function getTopOffice($level="main_office"){
        if($this->level=="branch"){
            return $this;
        }
        $parent = $this->getParent();
        if($parent == null){
            return $parent;
        }
        if($parent->level == $level){
         return $parent;
        }else{
            return  $parent->getTopOffice($level);
        }
    }
    
    public function generateClientID(){
        
    }
}
