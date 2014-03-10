<?php
namespace Keratine\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');

        $rootNode
            ->children()
                ->scalarNode('site_title')->end()
                ->scalarNode('root_dir')->end()
                ->booleanNode('debug')
                    ->defaultTrue()
                ->end()
                ->scalarNode('locale')
                    ->defaultValue('en')
                ->end()
                ->scalarNode('copyright')
                    ->defaultValue('2014 Keratine')
                ->end()
                ->scalarNode('version')
                    ->defaultValue('3.0-dev')
                ->end()
                ->arrayNode('credits')
                    ->children()
                        ->scalarNode('title')
                            ->defaultValue('Cellules.tv')
                        ->end()
                        ->scalarNode('url')
                            ->defaultValue('http://cellules.tv/')
                        ->end()
                    ->end()
                ->end()
                // Debug three
                ->arrayNode('monolog')
                    ->children()
                        ->scalarNode('logfile')->end()
                    ->end()
                ->end()
                ->arrayNode('profiler')
                    ->children()
                        ->scalarNode('cache_dir')->end()
                    ->end()
                ->end()
                // ZendSearch
                ->arrayNode('zendsearch')
                    ->children()
                        ->arrayNode('indices_path')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode->append($this->buildSessionTree());

        $rootNode->append($this->buildTwigTree());

        $rootNode->append($this->buildTranslatorTree());

        $rootNode->append($this->buildDatabaseTree());

        $rootNode->append($this->buildSecurityThree());

        $rootNode->append($this->buildMailerThree());

        return $treeBuilder;
    }

    private function buildSessionTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('session');

        $node
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('id')->end()
                ->scalarNode('cookie_lifetime')->end()
                ->scalarNode('cookie_path')->end()
                ->scalarNode('cookie_domain')->end()
                ->scalarNode('cookie_secure')->end()
                ->scalarNode('cookie_httponly')->end()
            ->end()
        ;

        return $node;
    }

    private function buildTwigTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('twig');

        $node
            ->children()
                ->arrayNode('path')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('templates')
                    ->prototype('array')->end()
                ->end()
                ->arrayNode('options')
                    ->children()
                        ->booleanNode('debug')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('charset')->end()
                        ->scalarNode('base_template_class')
                            ->defaultValue('Twig_Template')
                        ->end()
                        ->variableNode('cache')->end()
                        ->booleanNode('auto_reload')->end()
                        ->booleanNode('strict_variables')
                            ->defaultFalse()
                        ->end()
                        ->variableNode('autoescape')->end()
                        ->integerNode('optimizations')
                            ->defaultValue(-1)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('form')
                    ->children()
                        ->arrayNode('templates')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function buildTranslatorTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('translator');

        $node
            ->children()
                ->arrayNode('ressources')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function buildDatabaseTree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('database');

        $node
            ->children()
                ->arrayNode('options')
                    ->children()
                        ->scalarNode('driver')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('localhost')
                        ->end()
                        ->scalarNode('port')->end()
                        ->scalarNode('unix_socket')->end()
                        ->scalarNode('path')->end()
                        ->scalarNode('memory')->end()
                        ->scalarNode('sslmode')->end()
                        ->scalarNode('dbname')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('charset')
                            ->defaultValue('utf8')
                        ->end()
                        ->arrayNode('driverOptions')
                            ->children()
                                ->scalarNode('1002')
                                    ->defaultValue('SET NAMES utf8')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->append($this->buildOrmThree())
            ->end()
        ;

        return $node;
    }

    private function buildOrmThree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('orm');

        $node
            ->children()
                ->scalarNode('proxies_dir')
                     ->defaultValue('%root_dir%/var/cache/doctrine/proxy')
                ->end()
                ->scalarNode('proxies_namespace')
                     ->defaultValue('Doctrine\ORM\Proxy\Proxy')
                ->end()
                ->booleanNode('auto_generate_proxies')
                    ->defaultTrue()
                ->end()
                ->arrayNode('em')
                    ->children()
                        ->arrayNode('options')
                            ->children()
                                ->arrayNode('mappings')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('type')->end()
                                            ->scalarNode('namespace')->end()
                                            ->scalarNode('path')->end()
                                            ->booleanNode('use_simple_annotation_reader')
                                                ->defaultFalse()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function buildSecurityThree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('security');

        $node
            ->children()
                ->arrayNode('firewalls')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('pattern')->end()
                            ->arrayNode('form')
                                ->children()
                                    ->scalarNode('login_path')->end()
                                    ->scalarNode('check_path')->end()
                                ->end()
                            ->end()
                            ->arrayNode('logout')
                                ->children()
                                    ->scalarNode('logout_path')->end()
                                ->end()
                            ->end()
                            ->arrayNode('anonymous')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('encoder')
                    ->children()
                        ->scalarNode('digest')->end()
                    ->end()
                ->end()
                ->arrayNode('role_hierarchy')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('access_rules')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function buildMailerThree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('swiftmailer');

        $node
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('host')
                    ->defaultValue('localhost')
                ->end()
                ->integerNode('port')
                    ->defaultValue(25)
                ->end()
                ->scalarNode('username')->end()
                ->scalarNode('password')->end()
                ->scalarNode('encryption')->end()
                ->scalarNode('auth_mode')->end()
            ->end()
        ;

        return $node;
    }
}