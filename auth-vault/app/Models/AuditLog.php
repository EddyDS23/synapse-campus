<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

#[Fillable(['user_id','action','ip_address','service','resource_type','resource_id','metadata'])]
class AuditLog extends Model
{   

    use HasUlids;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
