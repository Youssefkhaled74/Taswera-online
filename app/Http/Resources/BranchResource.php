<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
            return [
                'id' => $this->id,
                'name' => $this->name,
                // 'is_active' => $this->is_active,
                'token' => $this->token,
                'manager_email' => $this->manager_email,
                'manager_password' => $this->manager_password,
                'admin_email' => $this->admin_email,
                'admin_password' => $this->admin_password,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
    }
}