<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'company_id'  => $this->company_id,
            'employee_id' => $this->employee_id,
            'sender_id'   => $this->sender_id,
            'message'     => $this->message,
            'is_read'     => $this->is_read,
            'sender'      => new UserResource($this->whenLoaded('sender')),
            'created_at'  => $this->created_at->format('d-m-Y H:i:s'),
        ];
    }
}
