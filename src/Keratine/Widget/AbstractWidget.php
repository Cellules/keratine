<?php
namespace Keratine\Widget;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractWidget
{
	protected $options;

	protected $container;

	public function __construct(array $options = array())
	{
		$resolver = new OptionsResolver();
		$this->setDefaultOptions($resolver);

		$this->options = $resolver->resolve($options);
	}

	protected function setDefaultOptions(OptionsResolverInterface $resolver)
	{

	}

	public function setContainer(\ArrayAccess $container)
	{
		$this->container = $container;
	}

	public function getEntityValue($entity, $column)
	{
		$accessor = PropertyAccess::createPropertyAccessor();
		return $accessor->getValue($entity, $column);
	}

	public abstract function render($entity, $column);
}