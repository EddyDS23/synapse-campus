<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['email','ip_address','reason','failed_at'])]
class LoginAttempt extends Model
{
    public $timestamps = false;
}

