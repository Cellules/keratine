<?php
namespace Keratine\Twig\Extension;

use Silex\Application;

class AssetsExtension extends \Twig_Extension
{
    private $app;
    private $options;

    function __construct(Application $app, array $options = array())
    {
        $this->app = $app;
        $this->options = $options;
    }

    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'asset'),
        );
    }

    public function asset($url)
    {
        $assetDir = isset($this->options['asset.directory']) ?
            $this->options['asset.directory'] :
            $this->app['request']->getBasePath();

        return sprintf('%s/%s', $assetDir, ltrim($url, '/'));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'assets';
    }
}