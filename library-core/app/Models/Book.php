<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['title','author','isbn','category','stock_total','stock_available'])]
class Book extends Model
{
    use HasUlids;

    public $timestamps = true;

    #[Override]
    protected function casts()
    {
        return [
            'stock_total'=>'integer',
            'stock_available'=>'integer'
        ];
    }

}
