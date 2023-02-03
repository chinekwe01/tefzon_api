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
        return [
            'id' => $this->id,
            'name' => $this->first_name . ' ' . $this->last_name,
            'username' => $this->username,
            'avatar' => $this->avatar,
            'email' => $this->email,
            'referral_link' => $this->referral_link,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'country' => $this->country];
    }
}
