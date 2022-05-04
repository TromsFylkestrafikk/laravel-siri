<?xml version="1.0" encoding="utf-8"?>
<Siri
  xmlns="http://www.siri.org.uk/siri"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  version="{{ $subscription->version }}"
  xsi:schemaLocation="http://www.siri.org.uk/siri ../siri.xsd"
>
  <SubscriptionRequest>
    <RequestTimestamp>{{ $request_date }}</RequestTimestamp>
    <RequestorRef>{{ $subscription->requestor_ref }}</RequestorRef>
    <MessageIdentifier>{{ $message_id }}</MessageIdentifier>
    <ConsumerAddress>{{ $consumer_address }}</ConsumerAddress>

    <SubscriptionContext>
      <HeartbeatInterval>{{ $subscription->heartbeat_interval }}</HeartbeatInterval>
    </SubscriptionContext>

    <VehicleMonitoringSubscriptionRequest>
      <SubscriberRef>{{ $subscription->requestor_ref }}</SubscriberRef>
      <SubscriptionIdentifier>{{ $subscription->subscription_ref }}</SubscriptionIdentifier>
      <InitialTerminationTime>{{ $subscription_ttl }}</InitialTerminationTime>

      <VehicleMonitoringRequest version="{{ $subscription->version }}">
        <RequestTimestamp>{{ $request_date }}</RequestTimestamp>
      </VehicleMonitoringRequest>

    </VehicleMonitoringSubscriptionRequest>
  </SubscriptionRequest>
</Siri>
