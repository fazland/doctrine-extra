<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\EventListener\Timestampable;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Fazland\DoctrineExtra\Timestampable\TimestampableInterface;

class TimestampUpdater
{
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if (! $object instanceof TimestampableInterface) {
            return;
        }

        $object->updateTimestamp();
    }
}
