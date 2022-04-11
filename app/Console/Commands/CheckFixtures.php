<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:fixtures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for latest fixtures';

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
       $point = new PointController();
       $point->checkfixtures();
    }
}
