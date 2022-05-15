<?php

namespace TromsFylkestrafikk\Siri\Subscription;

class VmRequest extends RequestBase implements SiriRequestContract
{
    /**
     * @inheritdoc
     */
    public function getViewData()
    {
        return array_merge(parent::getViewData(), [
            'update_interval' => 'PT1S',
        ]);
    }
}
