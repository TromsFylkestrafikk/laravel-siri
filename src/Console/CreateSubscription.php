<?php

namespace TromsFylkestrafikk\Siri\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DatePeriod;
use Ramsey\Uuid\Uuid;
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
                           { type : Either \'ET\' or \'VM\' }
                           { --H|heartbeat-interval= : Period between heartbeats from service }
                           { --r|requestor-ref= : Identifies client consuming siri data }
                           { --f|force : Create new subscription even if it exists for given type and service }';

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
        $exists = SiriSubscription::firstWhere([
            ['type', $this->argument('type')],
            ['subscription_address', $this->argument('url')]
        ]);
        if (
            $exists
            && !$this->option('force')
            && !$this->confirm(sprintf(
                "A previous subscription exists for this service and type with ID: %s\n Do you still want to create a new?",
                $exists->id
            ), false)
        ) {
            return 0;
        }
        $subscription = new SiriSubscription();
        $subscription->id = Uuid::uuid4();
        $subscription->fill([
            'type' => $this->argument('type'),
            'heartbeat_interval' => $this->getOptionOrConfig('heartbeat_interval'),
            'active' => true,
            'requestor_ref' => $this->getOptionOrConfig('requestor_ref'),
            'subscription_address' => $this->argument('url'),
        ]);
        $subscription->save();
        return 0;
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
