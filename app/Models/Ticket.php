<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function ticketReplies(): HasMany{
        return $this->hasMany(TicketReply::class);
    }
}
