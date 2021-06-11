<?php

namespace TromsFylkestrafikk\Siri\Subscription;

class EtRequest extends SiriRequestBase implements SiriRequestContract
{
    /**
     * {@inheritdoc}
     */
    public function getViewData()
    {
        return array_merge(parent::getViewData(), [
            'change_before_updates' => 20,
        ]);
    }
}
