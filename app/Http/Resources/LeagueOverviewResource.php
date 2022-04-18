<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeagueOverviewResource extends JsonResource
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
            "league" => $this->league->id,
            "league_name" => $this->league->name,
            "league_winner_type" => $this->league->winner_type,

            "league_status" => $this->league->status,

            'winner_id' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner->id : null),
            'winner' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner->username : null),
            'winner_price' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner_price : null),
            'winner_avatar' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner->avatar : null),
            'winner_email' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner->email : null
            ),
            'winner_phone' =>  $this->when(!is_null($this->winner), $this->winner ? $this->winner->phone : null),

            'second_id' =>  $this->when(!is_null($this->second), $this->second ? $this->second->id : null),
            'second' =>  $this->when(!is_null($this->second), $this->second ? $this->second->username : null),
            'second' =>  $this->when(!is_null($this->second), $this->second ? $this->second_price : null),
            'second_avatar' =>  $this->when(!is_null($this->second), $this->second ?  $this->second->avatar : null),
            'second_email' =>  $this->when(!is_null($this->second), $this->second ? $this->second->email : null),
            'second_phone' =>  $this->when(!is_null($this->second), $this->second ? $this->second->phone : null),

            'third_id' =>  $this->when(!is_null($this->third), $this->third ? $this->third->id : null),
            'third' =>  $this->when(!is_null($this->third), $this->third ?  $this->third->username : null),
            'third' =>  $this->when(!is_null($this->third), $this->third ?  $this->third_price : null),
            'third_avatar' =>  $this->when(!is_null($this->third), $this->third ?  $this->third->avatar : null),
            'third_email' =>  $this->when(!is_null($this->third), $this->third ? $this->third->email : null),
            'third_phone' =>  $this->when(!is_null($this->third), $this->third ? $this->third->phone : null),

        ];
    }

     public function with($request)
    {
        return [


            'meta' => [
                "league" => $this->league->id,
                "league_name" => $this->league->name,
                "league_winner_type" => $this->league->winner_type,
                "league_status" => $this->league->status,
            ]
        ];
    }
}
