<?php

namespace TromsFylkestrafikk\Siri\Console;

use Illuminate\Console\Command;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;
use TromsFylkestrafikk\Siri\Subscription\Subscriber;

class ReSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siri:resubscribe
                           { --c|close : SX only: close all existing situations prior to subscribe }
                           { id : SIRI channel to re-subscribe. See \'siri:list\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-subscribe existing subscription.';

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
        /** @var \TromsFylkestrafikk\Siri\Models\SiriSubscription $subscription */
        $subscription = SiriSubscription::find($this->argument('id'));
        if (!$subscription) {
            $this->warn("Subscription not found. See 'siri:list' for a list of current subscriptions.");
            return static::FAILURE;
        }
        if ($subscription->channel === 'SX' && $this->option('close')) {
            PtSituation::withoutGlobalScopes()
                ->where('progress', '<>', 'closed')
                ->update(['progress' => 'closed']);
        }
        $success = Subscriber::subscribe($subscription);
        if (!$success) {
            $this->warn(sprintf(
                "Re-subscription of SIRI-channel %s to %s failed. See log for further details.",
                $subscription->channel,
                $subscription->subscription_url
            ));
            return static::FAILURE;
        }
        $subscription->touch();
        $this->info(sprintf(
            "Successfully re-subscribed SIRI-channel %s to %s.",
            $subscription->channel,
            $subscription->subscription_url
        ));
        return static::SUCCESS;
    }
}
