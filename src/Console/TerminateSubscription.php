<?php

namespace TromsFylkestrafikk\Siri\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class TerminateSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siri:terminate
                            { id : Subscription identifier }
                            { --y|yes : Confirm all questions. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // @var \TromsFylkestrafikk\Siri\Models\SiriSubscription $subscription
        $subscription = SiriSubscription::find($this->argument('id'));
        if (!$subscription) {
            $this->warn("Subscription not found. See 'siri:list' for a list of current subscriptions.");
            return 1;
        }
        if (
            !$this->option('yes')
            && !$this->confirm(sprintf(
                "Really terminate %s subscription on %s?",
                $subscription->channel,
                $subscription->subscription_url,
            ), false)
        ) {
            return 0;
        }
        $subscription->delete();
        $msg = sprintf(
            "SIRI %s subscription to %s with ID %s was terminated.",
            $subscription->channel,
            $subscription->subscription_url,
            $subscription->id
        );
        Log::info($msg);
        $this->info($msg);
        return 0;
    }
}
