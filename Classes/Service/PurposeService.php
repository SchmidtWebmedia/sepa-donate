<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

class PurposeService
{
    public static function generate(): string
    {
        $year = (int)date('Y');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return sprintf('SPENDE-%d-%s', $year, $random);
    }
}
