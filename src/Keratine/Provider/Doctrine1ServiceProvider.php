<?php
namespace Keratine\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class Doctrine1ServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['doctrine.database'] = $app->share(function($app) {

			require_once($app['doctrine.options']['doctrine_path']);
			spl_autoload_register(array('Doctrine', 'autoload'));

			$app['doctrine.manager'] = \Doctrine_Manager::getInstance();

			// configuration
			$dsn = $app['doctrine.options']['dsn'];
			$username = $app['doctrine.options']['username'];
			$password = $app['doctrine.options']['password'];

			// connexion
			$dbh = new \PDO($dsn, $username, $password);
			$app['doctrine.connexion'] = \Doctrine_Manager::connection($dbh, 'doctrine');
			
			// retain username and password for Doctrine
			$app['doctrine.connexion']->setOption('username', $username);
			$app['doctrine.connexion']->setOption('password', $password);
			
			// charset
			$app['doctrine.connexion']->setCharset($app['doctrine.options']['charset']);
			
			// add quotes
			$app['doctrine.connexion']->setAttribute(\Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);

			if(!empty($app['doctrine.options']['prefix'])) {
				$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_TBLNAME_FORMAT, $app['doctrine.options']['prefix'].'_%s');
			}
			
			$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_EXPORT, \Doctrine_Core::EXPORT_ALL);
			$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_MODEL_LOADING, \Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
			
			$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true); // enable overide accessor methods
			
			$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
			
			$app['doctrine.manager']->setAttribute(\Doctrine_Core::ATTR_VALIDATE, \Doctrine_Core::VALIDATE_NONE);

			// model loading
			\Doctrine_Core::loadModels($app['doctrine.options']['model_dir']);
			spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
			
			// triggers the connection to be created (instantiate connection)
			$app['doctrine.connexion']->execute('SHOW TABLES');

			return $app['doctrine.connexion'];
		});

		$app['doctrine'] = $app->share(function ($app) {
			return $app['doctrine.database'];
		});
	}

	public function boot(Application $app)
    {
    	$app['doctrine'];
    }
}