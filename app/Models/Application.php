<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $primaryKey = 'slug';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
