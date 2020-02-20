<?php

namespace ScrapeKit\ScrapeKit\Http\Response\Parsers\Concerns;

interface ResponseValidationInterface
{

    public function validate(): bool;
}
