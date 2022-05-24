<?xml version="1.0"?>
<Siri xmlns="http://www.siri.org.uk/siri"
  xmlns:xs="http://www.w3.org/2001/XMLSchema-instance"
  version="{{ $version }}"
>
  <SubscriptionResponse xs:type="SubscriptionResponseStructure">
    <ResponseTimestamp>{{ $timestamp }}</ResponseTimestamp>
    <ResponderRef>SIRI Service provider</ResponderRef>
    <RequestMessageRef xs:type="MessageQualifierStructure">d8984743-c99b-4189-9a35-251f995ac88f</RequestMessageRef>
    <ResponseStatus xs:type="StatusResponseStructure">
      <ResponseTimestamp>{{ $timestamp }}</ResponseTimestamp>
      <SubscriberRef>Client company</SubscriberRef>
      <SubscriptionRef>0b985f97-ca46-4467-994a-4c908f346655</SubscriptionRef>
      <Status>true</Status>
      <ShortestPossibleCycle>PT1S</ShortestPossibleCycle>
    </ResponseStatus>
    <ServiceStartedTime>{{ $timestamp }}</ServiceStartedTime>
  </SubscriptionResponse>
</Siri>
