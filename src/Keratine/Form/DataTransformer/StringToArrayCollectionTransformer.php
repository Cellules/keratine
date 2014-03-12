<?php
namespace Keratine\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class StringToArrayCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $property;

    /**
     * @param ObjectManager $om
     * @param string $entityClass
     * @param string $property
     */
    public function __construct(ObjectManager $om, $entityClass, $property)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->property = $property;
    }

    /**
     * @param ArrayCollection $collection
     * @return string
     */
    public function transform($collection)
    {
        $values = array();

        foreach ($collection as $entity) {
            $values[] = $entity->getTitle();
        }

        return implode(',', $values);
    }

    /**
     * @param string $string
     * @return ArrayCollection
     */
    public function reverseTransform($string)
    {
        $values = explode(',', $string);

        $collection = new ArrayCollection();

        foreach ($values as $value) {
            $entity = $this->om->getRepository($this->entityClass)->findOneBy(array($this->property => $value));
            if (!$entity) {
                $entity = new $this->entityClass;
                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->setValue($entity, $this->property, $value);
                $this->om->persist($entity);
            }
            $collection->add($entity);
        }

        return $collection;
    }
}