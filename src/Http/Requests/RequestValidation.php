<?php

namespace ScrapeKit\ScrapeKit\Http\Requests;

interface RequestValidation
{

    public static function validateRequest(Request $request): bool;
}
