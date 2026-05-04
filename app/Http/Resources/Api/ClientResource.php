<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            // التأكد من إرجاع المسار الكامل للصورة إذا كانت موجودة
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
