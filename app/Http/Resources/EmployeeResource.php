<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name_prefix' => $this->name_prefix,
            'first_name' => $this->first_name,
            'middle_initial' => $this->middle_initial,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'time_of_birth' => $this->time_of_birth,
            'age' => $this->age,
            'date_of_joining' => $this->date_of_joining,
            'age_in_company' => $this->age_in_company,
            'phone_number' => $this->phone_number,
            'addresses' => AddressResource::collection(optional($this->addresses)),
        ];
    }
}
