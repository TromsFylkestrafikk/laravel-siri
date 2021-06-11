<?xml version="1.0" encoding="utf-8"?>
<AE:Siri version="1.4"
         xmlns:AB="http://www.ifopt.org.uk/ifopt"
         xmlns:AC="http://datex2.eu/schema/1_0/1_0"
         xmlns:AD="http://www.ifopt.org.uk/acsb"
         xmlns:AE="http://www.siri.org.uk/siri"
         xmlns:xs="http://www.w3.org/2001/XMLSchemainstance">
  <AE:SubscriptionRequest>
    <AE:RequestTimestamp>{{ $request_date }}</AE:RequestTimestamp>
    <AE:RequestorRef>{{ $subscription->requestor_ref }}</AE:RequestorRef>
    <AE:MessageIdentifier>{{ $message_identifier }}</AE:MessageIdentifier>
    <AE:ConsumerAddress>{{ $consumer_address }}</AE:ConsumerAddress>

    <AE:SubscriptionContext>
      <AE:HeartbeatInterval>{{ $subscription->heartbeat_interval }}</AE:HeartbeatInterval>
    </AE:SubscriptionContext>

    <AE:VehicleMonitoringSubscriptionRequest>
      <AE:SubscriberRef>{{ $subscription->requestor_ref }}</AE:SubscriberRef>
      <AE:SubscriptionIdentifier>{{ $subscription->id }}</AE:SubscriptionIdentifier>
      <AE:InitialTerminationTime>{{ $subscription_ttl }}</AE:InitialTerminationTime>

      <AE:VehicleMonitoringRequest version="1.4">
        <AE:RequestTimestamp>{{ $request_date }}</AE:RequestTimestamp>
      </AE:VehicleMonitoringRequest>

    </AE:VehicleMonitoringSubscriptionRequest>
  </AE:SubscriptionRequest>
</AE:Siri>
