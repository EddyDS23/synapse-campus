<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id','token_id','ip_address','device','last_used_at','created_at'])]
class ApiSession extends Model
{
    protected $table = 'api_sessions';
    public $timestamps = false;
}
