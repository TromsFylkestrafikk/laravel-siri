<?php

namespace TromsFylkestrafikk\Siri\Subscription;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use DOMDocument;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LogicException;
use SimpleXMLElement;

/**
 * Base class for all SIRI service channel requests.
 */
class SiriRequestBase
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
            'message_identifier' => "RequestorMsg",
            'subscription_ttl' => "2100-01-01T00:00:00.0",
            'request_date' => date('Y-m-d\TH:i:s'),
            'consumer_address' => url('siri/consume', [
                strtolower($this->subscription->channel),
                $this->subscription->id
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
        return view('siri::request.' . strtolower($this->subscription->channel), $this->getViewData())->render();
    }

    /**
     * @implements SiriRequestContract::sendRequest.
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
        Log::debug("Not sending actual request");
        return 200;

        $client = new HttpClient();
        $response = $client->post($this->subscription->subscription_address, [
            'body' => $dom->saveXml(),
            'connect_timeout' => 10,
            'headers' => [ 'Content-Type' => 'text/xml' ],
            'timeout' => 30,
        ]);
        if (($statusCode = $response->getStatusCode()) !== 200) {
            Log::warning(sprintf(
                "SiriRequest [%s]: Unexpected response status %d: %s",
                $this->subscription->subscriber_ref,
                $statusCode,
                $response->getReasonPhrase()
            ));
        } elseif (! $this->parseResponseXml($response->getBody())) {
            return 500;
        }
        return $statusCode;
    }

    protected function parseResponseXml($xmlStr)
    {
        // @var \SimpleXMLElement $xml
        $xml = simplexml_load_string($xmlStr);
        $sirins = array_search(self::NAMESPACE_SIRI, $xml->getNamespaces());
        $status = $xml->xpath("/$sirins:Siri[1]/$sirins:SubscriptionResponse[1]/$sirins:ResponseStatus[1]/$sirins:Status[1]");
        if (trim((string) $status[0]) === 'true') {
            return true;
        }

        $logMsg = "Siri subscription response XML error: ";
        $filename = sprintf(
            "Siri-%s-subscription-ERROR-%s.xml",
            $this->subscription->types->abbrevation,
            date('Y-m-d\TH:i:s')
        );
        $disk = config('siri.disk');
        Storage::disk($disk)->put($filename, $xmlStr);
        $logMsg .= sprintf("Response XML saved to disk '%s' as '%s'.", $disk, $filename);
        Log::error($logMsg);
        return false;
    }
}
