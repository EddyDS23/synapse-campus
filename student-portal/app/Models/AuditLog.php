<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['action','service','resource_type','resource_id','ip_address','metadata'])]
class AuditLog extends Model
{   
    use HasUlids;
    
    public $timestamps = false;

    #[Override]
    public function casts()
    {
        return [
            'metadata'=>'array',
        ];
    }


}
