<?php
namespace Keratine\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\MultiSearcher;
use ZendSearch\Lucene\Search\QueryParser;

class ZendSearchServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        Analyzer::setDefault(new CaseInsensitive());
        QueryParser::setDefaultEncoding('UTF-8');

        $app['zendsearch.indices_path'] = array();

        $app['zendsearch.indices.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            $indices = array();

            foreach ($app['zendsearch.indices_path'] as $name => $index) {
                $indices[$name] = file_exists($index) ? Lucene::open($index) : Lucene::create($index);
            }

            $app['zendsearch.indices_collection'] = $indices;
        });

        $app['zendsearch.indices'] = $app->share(function ($app) {
            $app['zendsearch.indices.initializer']();
            return $app['zendsearch.indices_collection'];
        });

        $app['zendsearch.multisearcher'] = $app->share(function ($app) {
            $app['zendsearch.indices.initializer']();
            $multi = new MultiSearcher();
            foreach ($app['zendsearch.indices'] as $index) {
                $multi->addIndex($index);
            }
            return $multi;
        });

        $app['zendsearch'] = $app->share(function ($app) {
            return $app['zendsearch.multisearcher'];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}