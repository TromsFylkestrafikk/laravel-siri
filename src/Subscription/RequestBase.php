<?php

namespace TromsFylkestrafikk\Siri\Subscription;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use SimpleXMLElement;

/**
 * Base class for all SIRI service channel requests.
 */
class RequestBase
{
    public const NAMESPACE_SIRI = 'http://www.siri.org.uk/siri';

    /**
     * The SIRI subscription we’re creating a request for.
     *
     * @var \TromsFylkestrafikk\Siri\Models\SiriSubscription
     */
    protected $subscription = null;

    /**
     * Data sent to request XML blade template.
     *
     * @var array
     */
    protected $viewData;

    /**
     * Current message identifier with siri service.
     *
     * @var string
     */
    protected $messageId = null;

    public function __construct(SiriSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the XML blade template data.
     *
     * @return array
     */
    public function getViewData()
    {
        $this->viewData = [
            'preview_interval' => 'PT24H',
            'is_incremental' => 'false',
            'message_id' => $this->messageId,
            'subscription_ttl' => "2100-01-01T00:00:00.0",
            'request_date' => date('Y-m-d\TH:i:s'),
            'consumer_address' => route('siri.consume', [
                'channel' => $this->subscription->channel,
                'subscription' => $this->subscription->subscription_ref
            ]),
            'subscription' => $this->subscription,
        ];
        return $this->viewData;
    }

    /**
     * Create the raw XML query string.
     *
     * @return string
     *   An XML siri request string.
     */
    public function generateRequestXml()
    {
        $this->messageId = Uuid::uuid4()->toString();
        return view(
            'siri::request.' . strtolower($this->subscription->channel),
            $this->getViewData()
        )->render();
    }

    /**
     * @implements SiriRequestContract<sendRequest>.
     */
    public function sendRequest(bool $dryRun = false)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->generateRequestXml());
        $dom->formatOutput = true;
        if ($dryRun) {
            return 200;
        }
        Log::debug(sprintf(
            "SiriRequest[%s]: Sending request to '%s' with message ID '%s'",
            $this->subscription->id,
            $this->subscription->subscription_url,
            $this->messageId
        ));
        $response = Http::withBody($dom->saveXML(), 'text/xml')->post($this->subscription->subscription_url);
        if (!$response->ok()) {
            Log::warning(sprintf(
                "SiriRequest [%s]: Unexpected response status %d: %s",
                $this->subscription->id,
                $response->status(),
                $response->toPsrResponse()->getReasonPhrase()
            ));
        } elseif (! $this->parseResponseXml($response->body())) {
            return 500;
        }
        return $response->status();
    }

    protected function parseResponseXml($xmlStr)
    {
        // @var \SimpleXMLElement $xml
        $xml = simplexml_load_string($xmlStr, SimpleXMLElement::class, 0, self::NAMESPACE_SIRI);
        $status = ((string) $xml->SubscriptionResponse->ResponseStatus->Status) ?: 'true';
        if (
            !$xml
            || !$xml->SubscriptionResponse
            || (string) $xml->SubscriptionResponse->RequestMessageRef !== $this->messageId
            || $status !== 'true'
        ) {
            Log::error(sprintf(
                "SIRI %s subscription to service '%s' failed. Dumping response XML.",
                $this->subscription->channel,
                $this->subscription->subscription_url
            ));
            $this->dumpResponseXml($xmlStr);
            return false;
        }
        Log::info(sprintf(
            "SIRI %s subscription to service '%s' was successfully created",
            $this->subscription->channel,
            $this->subscription->subscription_url
        ));
        return true;
    }

    /**
     * Dump response XML to disk.
     *
     * @param string $xmlStr the XML string to write.
     */
    protected function dumpResponseXml($xmlStr)
    {
        $filepath = sprintf(
            "siri/Siri-%s-subscription-ERROR-%s.xml",
            strtolower($this->subscription->channel),
            date('Y-m-d\TH:i:s')
        );
        $disk = config('siri.disk');
        Storage::disk($disk)->put($filepath, $xmlStr);
        Log::debug(sprintf("Response XML saved to disk '%s' as '%s'.", $disk, $filepath));
    }
}
