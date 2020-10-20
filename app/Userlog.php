<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Userlog extends Model
{
    protected $table = 'userlogs';
  
    protected $fillable = [
        'deviceID', 'email', 'username'
    ];
}
