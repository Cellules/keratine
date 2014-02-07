<?php
namespace Keratine\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;

use Silex\Application;

class ManagerRegistry extends AbstractManagerRegistry
{
    /**
     * @var Application
     */
    protected $container;
 
    /**
     * @inheritdoc
     */
    protected function getService($name)
    {
        return $this->container[$name];
    }
    
    /**
     * @inheritdoc
     */
    protected function resetService($name)
    {
        unset($this->container[$name]);
    }

    /**
     * @inheritdoc
     */
    public function setContainer(Application $container = null)
    {
        $this->container = $container;
    }
 
    public function getAliasNamespace($alias)
    {
        throw new \BadMethodCallException('Namespace aliases not supported.');
    }
}