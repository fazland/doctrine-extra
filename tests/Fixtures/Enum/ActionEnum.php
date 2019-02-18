<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\Tests\Fixtures\Enum;

use MyCLabs\Enum\Enum;

class ActionEnum extends Enum
{
    public const GET = 'get';
    public const POST = 'post';
}
