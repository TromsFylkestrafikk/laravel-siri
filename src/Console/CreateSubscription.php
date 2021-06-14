<?php

namespace TromsFylkestrafikk\Siri\Console;

use Closure;
use DateInterval;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Subscription\Subscriber;

class CreateSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siri:subscribe
                           { url : SIRI service subscription URL }
                           { channel : SIRI functional service. E.g. \'SX\' or \'VM\' }
                           { --H|heartbeat-interval= : Period (ISO 8601) between heartbeats from service }
                           { --r|requestor-ref= : Identifies client consuming siri data }
                           { --f|force : Create new subscription even if it exists for given channel and service }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new SIRI subscription';

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
        $channel = $this->getChannel();
        if (!$channel) {
            return 1;
        }
        $exists = SiriSubscription::firstWhere([
            ['channel', $channel],
            ['subscription_url', $this->argument('url')]
        ]);
        if (
            $exists
            && !$this->option('force')
            && !$this->confirm(sprintf(
                "A previous subscription exists for this service and channel with ID: %s\n Do you still want to create a new?",
                $exists->id
            ), false)
        ) {
            return 0;
        }
        $subscription = new SiriSubscription();
        $subscription->id = Uuid::uuid4();
        $subscription->fill([
            'channel' => $channel,
            'active' => true,
            'subscription_url' => $this->argument('url'),
            'heartbeat_interval' => $this->getHeartbeatInterval(),
            'requestor_ref' => $this->getOptionOrConfig('requestor_ref'),
        ]);
        $success = Subscriber::subscribe($subscription);
        if ($success) {
            $subscription->save();
            $this->info(sprintf(
                "SIRI %s subscription to %s was successfully created with ID: %s",
                $subscription->channel,
                $subscription->subscription_url,
                $subscription->id
            ));
        } else {
            $this->warn(sprintf(
                "SIRI %s subscription request failed. See log for further details.",
                $subscription->channel
            ));
        }
        return 0;
    }

    /**
     * Get SIRI channel
     */
    protected function getChannel()
    {
        $channel = strtoupper($this->argument('channel'));
        if (! in_array($channel, Subscriber::CHANNELS)) {
            $this->error(sprintf(
                "Unknown SIRI channel (%s). Must be one of: %s",
                $channel,
                implode(', ', Subscriber::CHANNELS)
            ));
            return false;
        }
        return $channel;
    }

    protected function getHeartbeatInterval()
    {
        return $this->getOptionOrConfig('heartbeat_interval', function ($interval) {
            new DateInterval($interval);
        });
    }

    /**
     * Get value from either command line option or from config.
     *
     * The key must match, though the format on command line is 'snake-case', vs
     * i config is 'kebab_case'.
     *
     * @param string $key  Name of option to get value for.
     * @param Closure $validate  Custom callback for validating user input.
     *                           This function should throw an exception on
     *                           input error, and the given error message will
     *                           be the user feedback.
     * @return mixed
     */
    protected function getOptionOrConfig($key, Closure $validate = null)
    {
        $camelKey = Str::camel($key);
        $value = $this->option(Str::kebab($camelKey));
        if ($value === null) {
            $value = config('siri.subscription.' . Str::snake($camelKey));
        } elseif ($validate) {
            $validate($value);
        }
        return $value;
    }
}
