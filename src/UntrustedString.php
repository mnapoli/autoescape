<?php declare(strict_types=1);

namespace Autoescape;

class UntrustedString
{
    public function __construct(private string $value)
    {
    }

    public function __toString()
    {
        // Encode the value in base64 to safely include it in the query
        // This will be decoded later when preparing the statement
        // to be turned into a placeholder and its corresponding parameter
        return sprintf('{escaped:%s}', base64_encode($this->value));
    }
}
