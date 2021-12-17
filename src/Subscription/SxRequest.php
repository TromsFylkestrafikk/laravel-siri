<?php

namespace TromsFylkestrafikk\Siri\Subscription;

class SxRequest extends RequestBase implements SiriRequestContract
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
