<?php

namespace TromsFylkestrafikk\Siri\Jobs;

use DateInterval;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Subscription\Subscriber;

class PeriodicResubscribe implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timeout = new DateTime();
        $timeout->sub(new DateInterval(config('siri.resubscribe_timeout', 'PT1H')));
        $subscriptions = SiriSubscription::whereActive(true)
            ->where('updated_at', '<', $timeout->format('Y-m-d H:i:s'))
            ->get();
        foreach ($subscriptions as $subscription) {
            $success = Subscriber::subscribe($subscription);
            if ($success) {
                $subscription->touch();
            }
        }
    }
}
