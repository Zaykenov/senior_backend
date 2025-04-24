<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'location',
        'capacity',
        'registration_deadline',
        'image',
        'organizer_info',
        'status'
    ];

    protected $casts = [
        'date' => 'datetime',
        'registration_deadline' => 'datetime',
        'capacity' => 'integer',
    ];

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the users (alumni) who have registered for this event.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
                    ->withTimestamps();
    }
} 