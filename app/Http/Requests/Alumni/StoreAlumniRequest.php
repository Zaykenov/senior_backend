<?php

namespace App\Http\Requests\Alumni;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlumniRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('alumni:create');
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'graduation_date' => 'required|date',
            'degree' => 'required|string|max:255',
            'faculty' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'email' => 'required|email|unique:alumni,email',
            'phone' => 'nullable|string|max:20',
            'current_job' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'biography' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
