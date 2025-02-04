<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Alumnus extends Model
{
    use HasTranslations;

    // Since the table name is not the plural of the model, explicitly define it:
    protected $table = 'alumni';

    protected $fillable = [
        'name', 'graduation_year', 'email', 'phone', 'address'
    ];

    public $translatable = ['name', 'address'];
}
