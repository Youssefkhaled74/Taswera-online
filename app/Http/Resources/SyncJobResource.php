<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'employeeName' => $this->employeeName,
            'pay_amount' => (float) $this->pay_amount,
            'orderprefixcode' => $this->orderprefixcode,
            'status' => $this->status,
            'shift_name' => $this->shift_name,
            'orderphone' => $this->orderphone,
            'number_of_photos' => $this->number_of_photos,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}