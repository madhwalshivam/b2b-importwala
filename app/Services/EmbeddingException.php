<?php

namespace App\Services;

class EmbeddingException extends \RuntimeException
{
    public function isUnavailable(): bool
    {
        return $this->getCode() === 503;
    }
}
