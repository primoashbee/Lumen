<?php

namespace App;

use App\Log;
use App\User;
use App\DataMigrationError;
use Illuminate\Database\Eloquent\Model;

class DataMigration extends Model
{
    protected $fillable = ['name','link','user_id'];

    public function logs(){
        return $this->morphMany(Log::class,'loggable');
    }

    public function error(){
        return $this->hasOne(DataMigrationError::class, 'migration_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
