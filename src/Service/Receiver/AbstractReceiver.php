<?php

namespace App\Service\Receiver;

use App\AbstractSystem;

abstract class AbstractReceiver extends AbstractSystem
{
    protected string $domain  = '';

    protected function getDefaultHeaders(): array
    {
        return [];
    }
}