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
    <Address>{{ $consumer_address }}</Address>

    <SubscriptionContext>
      <HeartbeatInterval>{{ $subscription->heartbeat_interval }}</HeartbeatInterval>
    </SubscriptionContext>

    <EstimatedTimetableSubscriptionRequest>
      <SubscriberRef>{{ $subscription->requestor_ref }}</SubscriberRef>
      <SubscriptionIdentifier>{{ $subscription->subscription_ref }}</SubscriptionIdentifier>
      <InitialTerminationTime>{{ $subscription_ttl }}</InitialTerminationTime>

      <EstimatedTimetableRequest version="{{ $subscription->version }}">
        <RequestTimestamp>{{ $request_date }}</RequestTimestamp>
        <MessageIdentifier>{{ $message_id }}</MessageIdentifier>
        <PreviewInterval>{{ $preview_interval }}</PreviewInterval>
      </EstimatedTimetableRequest>

      <IncrementalUpdates>{{ $is_incremental }}</IncrementalUpdates>
      <ChangeBeforeUpdates>{{ $change_before_updates }}</ChangeBeforeUpdates>
    </EstimatedTimetableSubscriptionRequest>
  </SubscriptionRequest>
</Siri>
