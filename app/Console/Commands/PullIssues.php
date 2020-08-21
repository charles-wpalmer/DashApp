<?php

namespace App\Console\Commands;

use App\Services\GitlabService;
use Illuminate\Console\Command;

class PullIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitlab:get:issues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets all of the issues from gitlab.';

    /**
     * @var GitlabService 
     */
    protected $service;

    /**
     * Create a new command instance.
     *
     * @param GitlabService $service
     */
    public function __construct()
    {
        parent::__construct();

        //$this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $s = resolve('Gitlab');
        $s->getIssues();
    }
}
