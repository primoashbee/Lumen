<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataMigrationError extends Model
{
    protected $fillable = ['migration_id','errors'];

    protected $casts = [
        'errors'=>'json',
        'errors.errors'=>'array'
    ];


}
