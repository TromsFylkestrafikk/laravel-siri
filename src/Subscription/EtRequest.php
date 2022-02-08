<?php

namespace TromsFylkestrafikk\Siri\Subscription;

class EtRequest extends RequestBase implements SiriRequestContract
{
    /**
     * {@inheritdoc}
     */
    public function getViewData()
    {
        return array_merge(parent::getViewData(), [
            'change_before_updates' => "PT30S",
        ]);
    }
}
