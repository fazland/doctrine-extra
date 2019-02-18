<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\EventListener\Timestampable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\MongoDB as MongoDBODM;
use Doctrine\ORM;
use Fazland\DoctrineExtra\Timestampable\TimestampableInterface;
use Fazland\ODM\Elastica as ElasticaODM;

class TimestampUpdater implements EventSubscriber
{
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if (! $object instanceof TimestampableInterface) {
            return;
        }

        $object->updateTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            ORM\Events::preUpdate,
            MongoDBODM\Events::preUpdate,
            ElasticaODM\Events::preUpdate,
        ];
    }
}
