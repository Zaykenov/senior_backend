<?php

namespace App\Http\Requests\Alumni;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Alumni;

class UpdateAlumniRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('alumni:edit');
    }

    public function rules()
    {
        $alumniId = $this->route('alumni'); // Get the alumni ID from the route parameter

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'graduation_date' => 'sometimes|required|date',
            'degree' => 'sometimes|required|string|max:255',
            'faculty' => 'sometimes|required|string|max:255',
            'major' => 'sometimes|nullable|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('alumni', 'email')->ignore($alumniId)
            ],
            'phone' => 'sometimes|nullable|string|max:20',
            'current_job' => 'sometimes|nullable|string|max:255',
            'company' => 'sometimes|nullable|string|max:255',
            'social_links' => 'sometimes|nullable|array',
            'biography' => 'sometimes|nullable|string',
            'profile_photo' => 'sometimes|nullable|image|max:2048',
            'country' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ];
    }
}