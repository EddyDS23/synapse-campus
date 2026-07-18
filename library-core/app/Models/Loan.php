<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['book_id','borrower_id','borrowed_at','due_at','renew_count','returned_at','status'])]
#[Hidden(['borrower_id','created_at','updated_at',])]
class Loan extends Model
{
    use HasUlids;

    public $timestamps = true;

    #[Override]
    protected function casts()
    {
        return [
            'borrowed_at'=>'datetime',
            'due_at'=>'datetime',
            'returned_at'=>'datetime',
            'renew_count'=>'integer'
        ];
    }

}
