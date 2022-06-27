<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LeagueController;

class UpdateLeagues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update leagues';

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
        $data= new LeagueController();
        $data->storeLeague();
        return 'ok';
    }
}
