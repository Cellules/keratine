<?php
namespace Keratine\Widget;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Set extends AbstractWidget
{
	protected function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(array('data'));

		$resolver->setAllowedTypes(array(
			'data' => 'array',
		));
	}

	public function render($entity, $column)
	{
		$value = $this->getEntityValue($entity, $column);

		if (is_array($value) || $value instanceof \Traversable) {
			$return = array();
			foreach ($value as $val) {
				$return[] = $this->renderValue($val);
			}
			return '<ul><li>' . implode('</li><li>', $return) . '</li></ul>';
		}

		return $this->renderValue($value);
	}

	protected function renderValue($value)
	{
		return isset($this->options['data'][$value]) ? $this->options['data'][$value] : $value;
	}
}