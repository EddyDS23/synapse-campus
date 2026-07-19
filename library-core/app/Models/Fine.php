<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['loan_id','borrower_id','amount','paid_at','status','debt_notified'])]
#[Hidden(['updated_at','loan_id','borrower_id','debt_notified'])]
class Fine extends Model
{
    use HasUlids;

    public $timestamps = true;

    #[Override]
    protected function casts()
    {
        return [
            'amount'=>'decimal:2',
            'paid_at'=>'datetime'
        ];
    }

}
