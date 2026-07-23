<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['ticket_id','author_id','body','is_internal'])]
#[Hidden(['is_internal'])]
class TicketComment extends Model
{
    use HasUlids;

    public $timestamps = true;

    #[Override]
    protected function casts()
    {
        return [
            'is_internal'=>'boolean'
        ];
    }

    public function ticket(){
        return $this->belongsTo(Ticket::class);
    }
}
