<?php

/**
 * @file
 * Definition of SiriRequestInterface.
 */

namespace TromsFylkestrafikk\Siri\Subscription;

/**
 * SIRI requests.
 */
interface SiriRequestContract
{
    /**
     * Send the subscription request to the SIRI partner.
     *
     * @param $dry_run
     *   If true, generate the xml for the request, but don't send it.
     *
     * @return int
     *   True on OK (200) and http status code on everything else.
     */
    public function sendRequest(bool $dry_run = false);
}
