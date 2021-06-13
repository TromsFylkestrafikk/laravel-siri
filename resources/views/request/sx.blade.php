<?xml version="1.0" encoding="utf-8"?>
<Siri version="1.4" xmlns="http://www.siri.org.uk/siri">
  <SubscriptionRequest>
    <RequestTimestamp>{{ $request_date }}</RequestTimestamp>
    <RequestorRef>{{ $subscription->requestor_ref }}</RequestorRef>
    <ConsumerAddress>{{ $consumer_address }}</ConsumerAddress>

    <SubscriptionContext>
      <HeartbeatInterval>{{ $subscription->heartbeat_interval }}</HeartbeatInterval>
    </SubscriptionContext>

    <SituationExchangeSubscriptionRequest>
      <SubscriberRef>{{ $subscription->requestor_ref }}</SubscriberRef>
      <SubscriptionIdentifier>{{ $subscription->id }}</SubscriptionIdentifier>
      <InitialTerminationTime>{{ $subscription_ttl }}</InitialTerminationTime>

      <SituationExchangeRequest version="1.4">
        <RequestTimestamp>{{ $request_date }}</RequestTimestamp>
        <PreviewInterval>{{ $preview_interval }}</PreviewInterval>
      </SituationExchangeRequest>

    </SituationExchangeSubscriptionRequest>
  </SubscriptionRequest>
</Siri>
