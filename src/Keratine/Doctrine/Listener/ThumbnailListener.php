<?php

namespace Keratine\Doctrine\Listener;

use ReflectionProperty;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Events;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ThumbnailListener implements EventSubscriber
{
    private $reader;


    public function setAnnotationReader($reader)
    {
        $this->reader = $reader;
    }


    public function getAnnotationReader()
    {
        if (!$this->reader) {
            $this->reader = new AnnotationReader();
        }

        return $this->reader;
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
        $entityManager = $args->getObjectManager();

        $this->createThumbnail($entityManager, $entity);
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
        $entityManager = $args->getObjectManager();

        $this->createThumbnail($entityManager, $entity);
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
        $entityManager = $args->getObjectManager();

        $meta = $entityManager->getClassMetadata(get_class($entity));

        foreach ($meta->fieldMappings as $field => $options) {
            $reflProperty = new ReflectionProperty($meta->getName(), $field);
            $annotation = $this->getAnnotationReader()->getPropertyAnnotation($reflProperty, '\Keratine\Doctrine\Mapping\Annotation\Thumbnail');
            if ($annotation) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $sourcePath = $accessor->getValue($entity, $annotation->path);

                if (!$sourcePath) return;

                foreach ($annotation->sizes as $name => $size) {
                    $destinationPath = dirname($sourcePath) .'/'. $name .'/'. basename($sourcePath);
                    if (file_exists($destinationPath)) {
                        unlink($destinationPath);
                    }
                }
            }
        }
    }


    private function createThumbnail(ObjectManager $entityManager, $entity)
    {
        $meta = $entityManager->getClassMetadata(get_class($entity));

        foreach ($meta->fieldMappings as $field => $options) {
            $reflProperty = new ReflectionProperty($meta->getName(), $field);
            $annotation = $this->getAnnotationReader()->getPropertyAnnotation($reflProperty, '\Keratine\Doctrine\Mapping\Annotation\Thumbnail');
            if ($annotation) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $sourcePath = $accessor->getValue($entity, $annotation->path);

                if (!$sourcePath) return;

                foreach ($annotation->sizes as $name => $size) {
                    if (!is_array($size) || count($size) !== 2) {
                        throw new \InvalidArgumentException('Property "sizes" in annotation "Thumbnail" must defined a set of arrays which defining two values for width and height.');
                    }
                    $destinationPath = dirname($sourcePath) .'/'. $name .'/'. basename($sourcePath);
                    $this->generateThumbnail($sourcePath, $destinationPath, $size[0], $size[1]);
                }
            }
        }
    }


    private function generateThumbnail($source, $destination, $width, $height)
    {
        $imagetype = exif_imagetype($source);
        switch ($imagetype) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($source);
                break;
            case IMAGETYPE_WBMP:
                $img = imagecreatefromwbmp($source);
                break;
            case IMAGETYPE_XBM:
                $img = imagecreatefromxbm($source);
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown image type for file "%s"', $source));
                break;
        }
        $sourceWidth = imagesx($img);
        $sourceHeight = imagesy($img);
        $height = floor($sourceHeight * ($width / $sourceWidth));
        $tmp = imagecreatetruecolor($width, $height);
        imageantialias($tmp, true);
        imagecopyresized($tmp, $img, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        if (!file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
            chmod(dirname($destination), 0755);
        }
        imagedestroy($img);
         switch ($imagetype) {
            case IMAGETYPE_JPEG:
                imagejpeg($tmp, $destination);
                break;
            case IMAGETYPE_PNG:
                imagepng($tmp, $destination);
                break;
            case IMAGETYPE_GIF:
                imagegif($tmp, $destination);
                break;
            case IMAGETYPE_WBMP:
                imagewbmp($tmp, $destination);
                break;
            case IMAGETYPE_XBM:
                imagexbm($tmp, $destination);
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown image type for file "%s"', $source));
                break;
        }
        imagedestroy($tmp);
    }
}