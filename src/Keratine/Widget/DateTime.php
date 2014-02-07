<?php
namespace Keratine\Widget;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateTime extends AbstractWidget
{
	protected function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setOptional(array('format'));

		$resolver->setDefaults(array(
			'format' => 'Y-m-d H:i:s',
		));

		$resolver->setAllowedTypes(array(
			'format' => 'string',
		));
	}

	public function render($entity, $column)
	{
		$date = $this->getEntityValue($entity, $column);

		if (!($date instanceOf \DateTime)) {
			$date = new \DateTime($date);
		}

		return $date->format($this->options['format']);
	}
}