<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['loan_id','borrower_id','previous_due_at','new_due_at'])]
class Renewal extends Model
{
    
    use HasUlids;

    public $timestamps = false;

    #[Override]
    protected function casts()
    {
        return [
            'previous_due_at'=>'datetime',
            'new_due_at'=>'datetime'
        ];
    }

}
