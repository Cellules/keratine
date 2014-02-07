<?php

namespace Keratine\Form\Type;

use Keratine\Form\EventListener\TranslationSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslatedEntityType extends AbstractType
{
    /**
     * @var \ArrayAccess
     */
    protected $container;

    /**
     * @param ArrayAccess $container
     */
    public function __construct(\ArrayAccess $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translatedEntity', 'hidden');

        $builder->addEventSubscriber(new TranslationSubscriber($options['locales'], $this->container['doctrine'], new \Doctrine\Common\Annotations\AnnotationReader(), $this->container['request']->getLocale(), $this->container['locale'], $options['translation_class']));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'locales'           => array(),
            'translation_class' => 'Gedmo\\Translatable\\Entity\\Translation',
            'mapped'            => false, // @TODO
            'label'             => false, // @TODO
            'attr'              => array(
                'class'  => 'translatable-fields' // @TODO
            ),
        ));
    }

    /**
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options = array())
    {
        return $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable_entity';
    }
}