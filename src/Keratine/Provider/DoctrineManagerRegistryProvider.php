<?php
namespace Keratine\Provider;

use Keratine\Persistence\DoctrineRegistry;
// use Keratine\Persistence\ManagerRegistry;

use Doctrine\ORM\EntityManager;

use Silex\Application;
use Silex\ServiceProviderInterface;

class DoctrineManagerRegistryProvider implements ServiceProviderInterface
{
	/**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['doctrine'] = $app->share(function($app) {
            
            // $managerRegistry = new ManagerRegistry(null, array(), array('orm.em'), null, null, $app['orm.proxies_namespace']);
            $managerRegistry = new DoctrineRegistry(null, array(), array('orm.em'), null, null, $app['orm.proxies_namespace']);

            $managerRegistry->setContainer($app);

    		return $managerRegistry;
        });
    }
}