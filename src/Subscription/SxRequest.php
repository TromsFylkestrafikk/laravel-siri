<?php

namespace TromsFylkestrafikk\Siri\Subscription;

class SxRequest extends RequestBase implements SiriRequestInterface
{
    /**
     * {@inheritdoc}
     */
    public function getViewData()
    {
        return array_merge(parent::getViewData(), [
            'preview_interval' => 'P1Y',
        ]);
    }
}
