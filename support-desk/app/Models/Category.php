<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['name','label'])]
#[Hidden(['created_at','updated_at'])]
class Category extends Model
{
    use HasUlids;

    public $timestamps = true;

}
