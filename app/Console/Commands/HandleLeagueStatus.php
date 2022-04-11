<?php

namespace App\Console\Commands;

use App\Models\League;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

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
            }
        }

        foreach ($activeleagues as $league) {
            $start = Carbon::parse($league->start);
            $end = Carbon::parse($league->end);
            if ($now->gte($end)) {
                $league->status = 'ended';
                $league->save();
            }
        }

        return 0;
    }
}
