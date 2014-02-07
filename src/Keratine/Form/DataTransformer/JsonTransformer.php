<?php
namespace Keratine\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class JsonTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!@$value = json_decode($value)) {
            throw new TransformationFailedException(sprintf(
                'Value "%s" is not a valid JSON string.',
                $value
            ));
        }

        return $value;
    }
}