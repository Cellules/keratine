<?php
namespace Keratine\Config;

use Keratine\Config\Configuration;
use Keratine\Config\Loader\YamlFileLoader;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
    private $path;

    private $files;

    private $parameterBag;


    public function __construct($path = array(), $files = array(), $replacements = array())
    {
        $this->path = $path;
        $this->files = $files;
        $this->parameterBag = new ParameterBag();
    }

    public function load()
    {
        $config = array();

        $locator = new FileLocator($this->path);
        $yamlLoader = new YamlFileLoader($locator);
        $loaderResolver = new LoaderResolver(array($yamlLoader));
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        foreach ($this->files as $filename) {
            $values = $delegatingLoader->load($this->path.'/'.$filename);
            $config = array_replace_recursive($config, $values);
        }

        $replacements = $this->parameterBag->all();

        array_walk_recursive($config, function (&$item, $key, $replacements) {
            foreach ($replacements as $needle => $replace) {
                if (false !== strpos($item, '%'.$needle.'%')) {
                    $item = str_replace('%'.$needle.'%', $replace, $item);
                }
            }
        }, $replacements);

        // process configuration
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array($config));

        return $config;
    }

    public function addParameter($name, $value)
    {
        $this->parameterBag->set($name, $value);
    }
}