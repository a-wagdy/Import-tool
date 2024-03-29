<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Address $this */
        return [
            'id' => $this->id,
            'place_name' => $this->place_name,
            'country' => $this->country,
            'city' => $this->city,
            'region' => $this->region,
            'zip' => $this->zip,
        ];
    }
}
