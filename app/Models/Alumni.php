<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Alumni extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'alumni';

    protected $fillable = [
        'first_name',
        'last_name',
        'graduation_date',
        'degree',
        'faculty',
        'major',
        'email',
        'phone',
        'current_job',
        'company',
        'social_links',
        'biography',
        'profile_photo',
        'country',
        'city',
        'user_id',
    ];

    protected $casts = [
        'graduation_date' => 'date',
        'social_links' => 'array',
    ];

    // Full name accessor
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}