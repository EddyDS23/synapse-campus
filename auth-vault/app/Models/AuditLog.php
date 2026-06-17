<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;


#[Fillable(['user_id','action','ip_address'])]
class AuditLog extends Model
{   
    public $timestamps = false;
}
