<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Override;

#[Fillable(['user_id','jti','ip_address','device','expires_at','refresh_token','refresh_expires_at','last_used_at','created_at'])]
class ApiSession extends Model
{

    use HasUlids;

    protected $table = 'api_sessions';
    public $timestamps = false;


    protected function casts():array{
        return [
            'expires_at'=>'datetime',
            'refresh_expires_at'=>'datetime',
            'last_used_at'=>'datetime',
            'created_at'=>'datetime',
        ];
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
