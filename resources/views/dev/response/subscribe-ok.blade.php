<?xml version="1.0"?>
<AA:Siri xmlns:AA="http://www.siri.org.uk/siri" xmlns:AD="http://www.ifopt.org.uk/acsb" xmlns:AC="http://datex2.eu/schema/1_0/1_0" xmlns:AB="http://www.ifopt.org.uk/ifopt" xmlns:xs="http://www.w3.org/2001/XMLSchema-instance" version="1.4">
  <AA:SubscriptionResponse xs:type="AA:SubscriptionResponseStructure">
    <AA:ResponseTimestamp>{{ $timestamp }}</AA:ResponseTimestamp>
    <AA:ResponderRef>SIRI Service provider</AA:ResponderRef>
    <AA:RequestMessageRef xs:type="AA:MessageQualifierStructure">RequestorMsg</AA:RequestMessageRef>
    <AA:ResponseStatus xs:type="AA:StatusResponseStructure">
      <AA:ResponseTimestamp>{{ $timestamp }}</AA:ResponseTimestamp>
      <AA:SubscriberRef>Client company</AA:SubscriberRef>
      <AA:SubscriptionRef>0b985f97-ca46-4467-994a-4c908f346655</AA:SubscriptionRef>
      <AA:Status>true</AA:Status>
      <AA:ShortestPossibleCycle>PT1S</AA:ShortestPossibleCycle>
    </AA:ResponseStatus>
    <AA:ServiceStartedTime>{{ $timestamp }}</AA:ServiceStartedTime>
  </AA:SubscriptionResponse>
</AA:Siri>
