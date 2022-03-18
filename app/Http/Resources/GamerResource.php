<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GamerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return ['id' => $this->id, 'name' => $this->name, 'username' => $this->username, 'profile' => $this->profile, 'email' => $this->email, 'referral_link' => $this->referral_link, 'phone' => $this->phone];
    }
}