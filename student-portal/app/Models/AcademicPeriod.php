<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['year','number','start_date','end_date','is_active'])]
class AcademicPeriod extends Model
{

    use HasUlids;
    
    #[Override]
    public function casts()
    {
        return [
            'start_date'=>'date',
            'end_date'=>'date',
            'is_active'=>'boolean'
        ];
    }

    public function sections(){
        return $this->hasMany(Section::class);
    }

}
