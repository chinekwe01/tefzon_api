<?php

namespace App\Http\Resources;

use App\Models\League;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function handlePoints($league_id, $user_id){
        $league = League::find($league_id);
        $from = Carbon::parse($league->start);
        $to = Carbon::parse($league->end);
       return $history = History::where('user_id', $user_id)->whereBetween('created_at', [$from, $to])->sum('points');

    }
    public function toArray($request)
    {

        return [

            "id" => $this->id,
            "gameweek" => $this->gameweek,
            "user_id" => $this->user_id,
            "league_id" => $this->league_id,
            "user" => $this->user->username,
            "updated_at" => $this->updated_at,
            "points" => $this->handlePoints($this->league_id,$this->user_id),
        ];
    }
}
