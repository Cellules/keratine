<?php
namespace Keratine\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

use Keratine\Form\EventListener\ImageCropListener;

class ImageCropType extends AbstractType
{
	/**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image_crop';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'image_path',
            'aspect_ratio'
        ));

        // $resolver->setDefaults(array(
        //     'aspect_ratio' => 1
        // ));
    }

	/**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('file', 'file');
    	$builder->add('left', 'hidden', array('attr' => array('class' => 'crop_left')));
    	$builder->add('top', 'hidden', array('attr' => array('class' => 'crop_top')));
    	$builder->add('width', 'hidden', array('attr' => array('class' => 'crop_width')));
    	$builder->add('height', 'hidden', array('attr' => array('class' => 'crop_height')));

    	$builder->addEventSubscriber(new ImageCropListener());
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['image_url'] = null;
		$view->vars['aspect_ratio'] = $options['aspect_ratio'];

		if (array_key_exists('image_path', $options))
		{
			$parentData = $form->getParent()->getData();

			if (!$parentData) {
				return false;
			}

			$propertyAccessor = PropertyAccess::getPropertyAccessor();
			$propertyPath = new PropertyPath($options['image_path']);

			$imageUrl = $propertyAccessor->getValue($parentData, $propertyPath);

			// définit une variable "image_url" qui sera disponible à l'affichage du champ
			// $view->set('image_url', $imageUrl);
			$view->vars['image_url'] = $imageUrl;
		}
	}
}