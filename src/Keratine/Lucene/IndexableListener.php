<?php

namespace Keratine\Lucene;

use ReflectionClass;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use ZendSearch\Lucene\SearchIndexInterface;

class IndexableListener implements EventSubscriber
{
    private $indices;

    private $reader;

    public function __construct(array $indices = array())
    {
        $this->indices = $indices;
        $this->reader = new AnnotationReader();
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        );
    }

    /**
     * Indexing document on objects being pesisted
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($index = $this->getIndexForObject($entity)) {
            $indexManager = new IndexManager($index);
            $indexManager->index($entity);
        }
    }

    /**
     * Indexing document on objects being updated after update if they require changing
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($index = $this->getIndexForObject($entity)) {
            $indexManager = new IndexManager($index);
            $indexManager->index($entity);
        }
    }

    /**
     * Delete document on objects after being remove
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($index = $this->getIndexForObject($entity)) {
            $indexManager = new IndexManager($index);
            $indexManager->remove($entity);
        }
    }

    /**
     * Gets the Lucene index to the related object
     *
     * @param object $object
     *
     * @return ZendSearch\Lucene\SearchIndexInterface
     */
    private function getIndexForObject($object)
    {
        $reflClass = new ReflectionClass($object);

        $annotation = $this->reader->getClassAnnotation($reflClass, '\Keratine\Lucene\Mapping\Annotation\Indexable');

        if (!$annotation) {
            // throw new \Exception(sprintf('%s must define annotation @%s', get_class($object), '\Keratine\Lucene\Mapping\Annotation\Indexable'));
            return;
        }

        if (empty($annotation->index)) {
            AnnotationException::requiredError('index', '\Keratine\Lucene\Mapping\Annotation\Indexable', $object, 'string');
        }

        if(false === isset($this->indices[$annotation->index])) {
            throw new \Exception(sprintf('Unknown index "%s".', $annotation->index));
        }

        if (false === $this->indices[$annotation->index] instanceof SearchIndexInterface) {
            throw new \Exception(
                sprintf('Index "%s" must be an instance of "ZendSearch\Lucene\SearchIndexInterface". "%s" given.',
                    $annotation->index,
                    is_object($this->indices[$annotation->index]) ? get_class($this->indices[$annotation->index]) : $this->indices[$annotation->index]
                )
            );
        }

        return $this->indices[$annotation->index];
    }
}
