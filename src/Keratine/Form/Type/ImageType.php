<?php
namespace Keratine\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImageType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'image';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return 'file';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setOptional(array(
			'image_path'
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['image_url'] = null;

		if (array_key_exists('image_path', $options))
		{
			$parentData = $form->getParent()->getData();

			if (!$parentData) {
				return false;
			}

			$accessor = PropertyAccess::createPropertyAccessor();
			$imageUrl = $accessor->getValue($parentData, $options['image_path']);

			// définit une variable "image_url" qui sera disponible à l'affichage du champ
			// $view->set('image_url', $imageUrl);
			$view->vars['image_url'] = $imageUrl;
		}
	}
}