<?php

namespace BlackpigCreatif\Confessionnal\Contracts;

interface CanReceiveSubmissions
{
    public static function getMappingName(): string;

    /** @return array<string, string> Column name => Display label */
    public static function getMappableColumns(): array;
}
