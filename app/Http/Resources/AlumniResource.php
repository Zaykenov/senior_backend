<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumniResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'graduation_date' => $this->graduation_date,
            'degree' => $this->degree,
            'faculty' => $this->faculty,
            'major' => $this->major,
            'email' => $this->email,
            'phone' => $this->phone,
            'current_job' => $this->current_job,
            'company' => $this->company,
            'social_links' => $this->social_links,
            'biography' => $this->biography,
            'profile_photo' => $this->profile_photo,
            'country' => $this->country,
            'city' => $this->city,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
