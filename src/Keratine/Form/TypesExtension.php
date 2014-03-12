<?php
namespace Keratine\Form;

use ArrayAccess;

use Silex\Application;

use Symfony\Component\Form\AbstractExtension;

class TypesExtension extends AbstractExtension
{
	/**
     * @var \ArrayAccess
     */
    protected $container;

	public function __construct(ArrayAccess $container)
	{
		$this->setContainer($container);
	}

	public function setContainer(ArrayAccess $container = null)
    {
        $this->container = $container;
    }

    protected function loadTypes()
    {
        return array(
            new Type\BootstrapCollectionType(),
            new Type\ColorType(),
            new Type\DurationType(),
            new Type\FileExplorerType(),
            new Type\ImageCropType(),
            new Type\ImageType(),
            new Type\TextboxListType(),
            new Type\TranslatedEntityType($this->container),
        );

    }

}