<?php
namespace Keratine\Application;

use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;

use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapFormExtension;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;

use Gedmo\Sluggable\SluggableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Timestampable\TimestampableListener;
use Gedmo\Translatable\TranslatableListener;

use Keratine\Form\TypesExtension;
use Keratine\Lucene\IndexableListener;
use Keratine\Doctrine\Listener\ThumbnailListener;
use Keratine\Persistence\DoctrineRegistry;
use Keratine\Provider\DoctrineManagerRegistryProvider;
use Keratine\Provider\UserProvider;
use Keratine\Provider\ZendSearchServiceProvider;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Pimple;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

use SilexAssetic\AsseticServiceProvider;

use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

class Application extends SilexApplication
{
     public function __construct($config = array(), array $values = array())
    {
        parent::__construct($values);

        $app = $this;

        $app['site_title'] = $config['site_title'];

        $app['copyright'] = $config['copyright'];

        $app['version'] = $config['version'];

        $app['credits'] = array(
            'title' => $config['credits']['title'],
            'url'   => $config['credits']['url'],
        );

        // enable the debug mode
        $app['debug'] = $config['debug'];

        $app['locale'] = $config['locale'];

        $app->register(new UrlGeneratorServiceProvider());

        $app->register(new ValidatorServiceProvider());

        $app->register(new ServiceControllerServiceProvider());

        $app->register(new SessionServiceProvider(), array(
            'session.storage.options' => $config['session']
        ));

        $app->register(new FormServiceProvider());

        // Twig
        $app->register(new TwigServiceProvider(), array(
            'twig.path'           => $config['twig']['path'],
            'twig.templates '     => $config['twig']['templates'],
            'twig.options'        => $config['twig']['options'],
            'twig.form.templates' => $config['twig']['form']['templates'],
        ));
        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            // add custom globals, filters, tags, ...
            $twig->addGlobal('site_title', $app['site_title']);
            // $twig->addExtension(new \Keratine\Twig\BootstrapFormExtension());
            $twig->addExtension(new BootstrapFormExtension());
            return $twig;
        }));

        // Assetic
        $app->register(new AsseticServiceProvider(), array(
            'assetic.path_to_web' => __DIR__ . '/..',
            'assetic.options' => array(
                'auto_dump_assets' => false, //!\\ gros ralentissements quand défini à true //!\\
                'debug' => false
            ),
            'assetic.filters' => $app->protect(function($fm) {
                // $fm->set('cssmin', new Assetic\Filter\CssMinFilter());
                // $fm->set('jsmin', new Assetic\Filter\JsMinFilter());
            })
        ));
        $app['assetic.asset_manager'] = $app->share(
            $app->extend('assetic.asset_manager', function($am, $app) {
                $am->set('jquery', new FileAsset('js/vendor/jquery-min.js'));
                $am->set('bootstrap_css', new GlobAsset('css/bootstrap/*.css'));
                $am->set('bootstrap_js', new AssetCollection(array(
                    new AssetReference($am, 'jquery'),
                    new GlobAsset('js/bootstrap/*.js')
                )));
                $am->set('admin_css', new GlobAsset('css/admin/*.css'));
                $am->set('admin_js', new FileAsset('js/admin/admin.js'));
                $am->get('jquery')->setTargetPath('assets/js/jquery.js');
                $am->get('bootstrap_css')->setTargetPath('assets/css/bootstrap.css');
                $am->get('bootstrap_js')->setTargetPath('assets/js/bootstrap.js');
                $am->get('admin_css')->setTargetPath('assets/css/admin.css');
                $am->get('admin_js')->setTargetPath('assets/js/admin.js');
                return $am;
            })
        );

        // Translation
        $app->register(new TranslationServiceProvider(), array(
            'locale_fallback' => $app['locale'],
        ));
        $app['translator'] = $app->share($app->extend('translator', function($translator) use ($app, $config) {
            $translator->addLoader('yaml', new YamlFileLoader());
            foreach ($config['translator']['ressources'] as $lang => $file) {
                $translator->addResource('yaml', $file, $lang);
            }
            return $translator;
        }));

        // ZendSearch
        $app->register(new ZendSearchServiceProvider(), array(
            'zendsearch.indices_path' => $config['zendsearch']['indices_path'],
        ));

        // Doctrine DBAL
        $app->register(new DoctrineServiceProvider(), array(
            'db.options' => $config['database']['options']
        ));

        // Doctrine ORM
        $app->register(new DoctrineOrmServiceProvider, array(
            'orm.proxies_dir'           => $config['database']['orm']['proxies_dir'],
            'orm.proxies_namespace'     => $config['database']['orm']['proxies_namespace'],
            'orm.auto_generate_proxies' => $config['database']['orm']['auto_generate_proxies'],
            'orm.cache'                 => !$app['debug'] && extension_loaded('apc') ? new ApcCache() : new ArrayCache(),
            // 'orm.em.options' => array(
            //     'mappings' => array(
            //         array(
            //             'type'      => 'annotation',        // entity definition
            //             'path'      => __DIR__ . '/Entity', // path to your entity classes
            //             'namespace' => 'Entity',            // your classes namespace
            //             'use_simple_annotation_reader' => false
            //         ),
            //         array(
            //             'type'      => 'annotation',
            //             'namespace' => 'Gedmo\Translatable\Entity',
            //             'path'      => __DIR__ . '/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity/MappedSuperclass',
            //             'use_simple_annotation_reader' => false
            //         ),
            //     ),
            // ),
            'orm.em.options' => $config['database']['orm']['em']['options'],
        ));

        //Setting Doctrine2 extensions

        $annotationReader = new AnnotationReader();
        $cachedAnnotationReader = new CachedReader($annotationReader, $app['orm.cache']);

        // sluggable
        $sluggableListener = new SluggableListener();
        $sluggableListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($sluggableListener);

        // sortable
        $sortableListener = new SortableListener();
        $sortableListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($sortableListener);

        // loggable
        $loggableListener = new LoggableListener;
        $loggableListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($loggableListener);

        // timestampable
        $timestampableListener = new TimestampableListener;
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($timestampableListener);

        // translatable
        $translatableListener = new TranslatableListener();
        $translatableListener->setTranslatableLocale($app['locale']);
        $translatableListener->setDefaultLocale($app['locale']);
        $translatableListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($translatableListener);

        // Lucene Indexable
        $luceneListener = new IndexableListener( $app['zendsearch.indices'] );
        $app['db.event_manager']->addEventSubscriber($luceneListener);

        // Thumbnail
        $thumbnailListener = new ThumbnailListener();
        $thumbnailListener->setAnnotationReader($cachedAnnotationReader);
        $app['db.event_manager']->addEventSubscriber($thumbnailListener);

        // DoctrineManagerRegistry
        $app->register(new DoctrineManagerRegistryProvider());

        // Doctrine ORM Form extensions
        $app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($app) {
            $extensions[] = new DoctrineOrmExtension($app['doctrine']);
            $extensions[] = new TypesExtension($app);
            return $extensions;
        }));

        // UserProvider
        $app['users'] = $app->share(function () use ($app) {
            return new UserProvider($app, 'Entity\User');
        });

        // User
        $app['user'] = $app->share(function($app) {
            return ($app['users']->getCurrentUser());
        });

        foreach ($config['security']['firewalls'] as $firemall => $options) {
            $config['security']['firewalls'][$firemall]['users'] = $app['users'];
        }

        // Security
        $app->register(new SecurityServiceProvider());

        if (isset($config['security']['firewalls'])) {
            $app['security.firewalls'] = $config['security']['firewalls'];
        }

        if (isset($config['security']['role_hierarchy'])) {
            $app['security.role_hierarchy'] = $config['security']['role_hierarchy'];
        }

        if (isset($config['security']['access_rules'])) {
            $app['security.access_rules'] = $config['security']['access_rules'];
        }

        if (isset($config['security']['encoder']['digest'])) {
            $app['security.encoder.digest'] = $config['security']['encoder']['digest'];
        }

        $app->register(new SwiftmailerServiceProvider(), $config['swiftmailer']);

        /*
         * Debug environment
         */
        if ($app['debug']) {

            $app->register(new MonologServiceProvider(), array(
                'monolog.logfile' => $config['monolog']['logfile'],
            ));

            $app->register($p = new WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $config['profiler']['cache_dir'],
            ));
            $app->mount('/_profiler', $p);

        }

        return $app;
    }
}