<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Schedule extends Model
{
    use HasFactory;

    function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    function internalMembers(): HasMany
    {
        return $this->hasMany(InternalMember::class, 'schedule_id', 'id');
    }

    function internalMember(): BelongsTo
    {
        return $this->belongsTo(InternalMember::class, 'id', 'schedule_id')
            ->where('user_id', Auth::user()->id);
    }

    function externalMembers(): HasMany
    {
        return $this->hasMany(ExternalMember::class, 'schedule_id', 'id');
    }

    function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function notulens() : HasMany {
        return $this->hasMany(Notulen::class, 'schedule_id', 'id');
    }
}
