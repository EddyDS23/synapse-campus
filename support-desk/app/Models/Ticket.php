<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable(['requester_id','assignee_id','category_id','priority','status','title','description','resolved_at','closed_at'])]
#[Hidden(['created_at','updated_at'])]
class Ticket extends Model
{
    use HasUlids;
    
    public $timestamps = true;

    #[Override]
    protected function casts()
    {
        return [
            'resolved_at'=>'datetime',
            'closed_at'=>'datetime',
            'created_at'=>'datetime'
        ];
    }


    public function comments(): HasMany{
        return $this->hasMany(TicketComment::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
