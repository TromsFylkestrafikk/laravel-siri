<?php

namespace TromsFylkestrafikk\Siri\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DatePeriod;
use Ramsey\Uuid\Uuid;
use TromsFylkestrafikk\Siri\Subscriber;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class CreateSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siri:subscribe
                           { url : SIRI service to subscribe to }
                           { channel : Either \'ET\' or \'VM\' }
                           { --H|heartbeat-interval= : Period between heartbeats from service }
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
            ['subscription_address', $this->argument('url')]
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
            'subscription_address' => $this->argument('url'),
            'heartbeat_interval' => $this->getOptionOrConfig('heartbeat_interval'),
            'requestor_ref' => $this->getOptionOrConfig('requestor_ref'),
        ]);
        $subscription->save();
        return 0;
    }

    /**
     * Get SIRI channel
     */
    protected function getChannel()
    {
        $channel = $this->argument('channel');
        if (! in_array($channel, ['et', 'vm'])) {
            $this->error(sprintf(
                "Unknown SIRI channel (%s). Must be one of: %s",
                $channel,
                implode(', ', Subscriber::CHANNELS)
            ));
            return false;
        }
        return $channel;
    }

    protected function getOptionOrConfig($key)
    {
        $camelKey = Str::camel($key);
        $value = $this->option(Str::kebab($camelKey));
        if ($value === null) {
            $value = config('siri.subscription.' . Str::snake($camelKey));
        }
        return $value;
    }
}
