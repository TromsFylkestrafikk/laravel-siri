<?php

namespace TromsFylkestrafikk\Siri\Console;

use Illuminate\Console\Command;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class ListSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siri:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List SIRI subscriptions';

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
        $this->table(
            ['ID', 'Channel', 'Service URL', 'Active', 'Count', 'Created', 'Last communication'],
            SiriSubscription::all([
                'id',
                'channel',
                'subscription_url',
                'active',
                'received',
                'created_at',
                'updated_at',
            ])->toArray()
        );
    }
}
