<?php
namespace Keratine\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;

use Keratine\Form\DataTransformer\StringToArrayCollectionTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextboxListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'textboxlist';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new StringToArrayCollectionTransformer($options['om'], $options['entityClass'], $options['property']);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'om',
            'entityClass',
            'property',
        ));

        $resolver->setAllowedTypes(array(
            'om' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }
}