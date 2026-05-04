<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotteryDrawResource extends JsonResource
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
            'client_id' => $this->client_id,
            // نقوم بتضمين بيانات العميل الفائز باستخدام المورد الخاص به
            'client' => new ClientResource($this->whenLoaded('client')),
            'draw_date' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
