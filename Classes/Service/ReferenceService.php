<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

class ReferenceService
{
    public static function generate(): string
    {
        $year    = (int)date('Y');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        return sprintf('SPENDE-%d-%s', $year, $random);
    }
}
