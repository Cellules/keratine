<?php
namespace Keratine\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ColorType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'color';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return 'text';
	}

    // public function setDefaultOptions(OptionsResolverInterface $resolver)
    // {
    //     $resolver->setDefaults(array(
    //         'attr' => array(
    //             'col_size'   => 'xs',
    //             'simple_col' => 2,
    //         )
    //     ));
    // }
}