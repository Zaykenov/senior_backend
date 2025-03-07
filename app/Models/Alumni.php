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
    ];

    protected $casts = [
        'graduation_date' => 'date',
        'social_links' => 'array',
    ];

    // Fields that should be translatable
    public $translatable = [
        'biography',
        'current_job',
    ];

    // Full name accessor
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}