<?php
namespace Keratine\Form\Type;

use Keratine\Form\DataTransformer\TimeToIntegerTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DurationType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'duration';
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
        $transformer = new TimeToIntegerTransformer();
        $builder->addModelTransformer($transformer);
    }
}