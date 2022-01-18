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
        // @var \Illuminate\Database\Eloquent\Collection $subscriptions
        $subscriptions = SiriSubscription::all();
        $toPrint = [];
        foreach ($subscriptions as $subscription) {
            $toPrint[] = [
                $subscription->id,
                $subscription->channel,
                $subscription->name,
                $subscription->isActive,
                $subscription->received,
                $subscription->created_at,
                $subscription->updated_at,
            ];
        }
        $this->table(
            ['ID', 'Channel', 'Name', 'Active', 'Count', 'Created', 'Last communication'],
            $toPrint
        );
        return static::SUCCESS;
    }
}
