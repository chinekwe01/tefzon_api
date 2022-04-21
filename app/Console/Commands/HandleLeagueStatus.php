<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Notifications\LeagueEnd;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Notifications\LeagueStart;
use Illuminate\Support\Facades\Notification;

class HandleLeagueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'league:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle league Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pendingleagues = League::where('status', 'pending')->get();
        $activeleagues = League::where('status', 'active')->get();

        $now = Carbon::now();
        foreach ($pendingleagues as $league) {
            $start = Carbon::parse($league->start);
            $end = Carbon::parse($league->end);
            if ($now->gte($start) && $now->lte($end)) {
                $league->status = 'active';
                $league->save();

                $users = $league->users()->get();
                $detail = [
                    'body' => ucfirst($league->name).' league has started'
                ];
                Notification::send($users, new LeagueStart($detail));
            }
        }

        foreach ($activeleagues as $league) {
            $start = Carbon::parse($league->start);
            $end = Carbon::parse($league->end);
            if ($now->gte($end)) {
                $handleending = new LeagueOverviewController();
                $handleending->handleLeagueEnding($league->id);
                $league->status = 'ended';
                $league->save();

                $users = $league->users()->get();
                $detail = [
                    'body' => ucfirst($league->name) . ' league has ended'
                ];
                Notification::send($users, new LeagueEnd($detail));
            }
        }

        return 0;
    }
}
