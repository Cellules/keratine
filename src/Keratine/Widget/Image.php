<?php
namespace Keratine\Widget;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Image extends AbstractWidget
{
	protected function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(array('image_path'));

		$resolver->setAllowedTypes(array(
			'image_path' => 'string',
		));
	}

	public function render($entity, $column)
	{
        $path = $this->getEntityValue($entity, $this->options['image_path']);

        if (!$path) return;

		$imageUrl =  $this->container['request']->getBasepath() . '/' . $path;

		return sprintf('<img class="thumbnail" src="%s" alt="" />', $imageUrl);
	}
}