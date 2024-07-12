<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'event_id',
        'summary',
        'location',
        'description',
        'start',
        'end',
        'timezone_code',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
