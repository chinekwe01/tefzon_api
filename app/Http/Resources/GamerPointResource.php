<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GamerPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function handlegameweek($arr){

    }
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'gameweek' =>  $this->gameweek,
            'player_name' =>   $this->player_name,
            'point' =>   $this->point,
            'player_position' =>  $this->player_position,
            'position' =>  $this->position,
            'position_id' =>  $this->position_id,
            'player_id' =>  $this->player_id,
            'is_captain' =>  $this->is_captain,
            'player_name' =>   $this->player_name,
            'is_vice_captain' => $this->is_vice_captain,
            'user_id' => $this->user_id,
            'gamer_squad_id' =>  $this->gamer_squad_id,
            'image_path' =>  $this->image_path,
            'is_starting' =>  $this->is_starting,
            'gameweekpoint' =>  $this->gameweekpoint,
        ];
    }
}
