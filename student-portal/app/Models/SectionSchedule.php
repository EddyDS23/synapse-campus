<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['section_id','day_of_week','start_time','end_time'])]
class SectionSchedule extends Model
{
    
    use HasUlids;

    #[Override]
    public function casts()
    {
        return [
            'start_time'=>'datetime:H:i:s',
            'end_time'=>'datetime:H:i:s'
        ];
    }

    public function section(){
        return $this->belongsTo(Section::class);
    } 
}
