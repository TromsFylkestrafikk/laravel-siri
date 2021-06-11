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
    public function sendRequest(bool $dry_run = false)
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->generateRequestXml());
        $dom->formatOutput = true;
        if ($dry_run) {
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
        if (($status_code = $response->getStatusCode()) !== 200) {
            Log::warning(sprintf(
                "SiriRequest [%s]: Unexpected response status %d: %s",
                $this->subscription->subscriber_ref,
                $status_code,
                $response->getReasonPhrase()
            ));
        } elseif (! $this->parseResponseXml($response->getBody())) {
            return 500;
        }
        return $status_code;
    }

    protected function parseResponseXml($xml_str)
    {
        // @var \SimpleXMLElement $xml
        $xml = simplexml_load_string($xml_str);
        $sirins = array_search(self::NAMESPACE_SIRI, $xml->getNamespaces());
        if (
            ($status = $xml->xpath("/$sirins:Siri[1]/$sirins:SubscriptionResponse[1]/$sirins:ResponseStatus[1]/$sirins:Status[1]")) &&
            (trim((string) $status[0]) === 'true')
        ) {
            return true;
        }

        $log_msg = "Siri subscription response XML error: ";
        if (
            $this->subscription->partners->name === 'Consat' &&
            ($errors = $xml->xpath("/$sirins:Siri[1]/$sirins:Extensions[1]/ITS4mobilityError[1]"))
        ) {
            $log_msg .= "\n{$errors[0]}\n";
        }
        $filename = sprintf(
            "Siri-%s-subscr-ERROR-%s.xml",
            $this->subscription->types->abbrevation,
            date('Y-m-d\TH:i:s')
        );
        Storage::disk('tmp')->put($filename, $xml_str);
        $log_msg .= "Response XML saved to [TMP]/$filename";
        Log::error($log_msg);
        return false;
    }
}
