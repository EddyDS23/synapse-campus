<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['owner_id','original_name','stored_name','extension','mime_type','size','path','disk'])]
#[Hidden(['path','disk'])]
class File extends Model
{
    use HasUlids;

    public $timestamps = false;

    #[Override]
    protected function casts()
    {
        return [
            'size'=>'int',
            'created_at'=>'datetime'
        ];
    }
}
