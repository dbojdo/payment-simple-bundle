<?php

namespace Webit\Accounting\PaymentSimpleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('webit_accounting_payment_simple');
// 		$rootNode->children()
// 			->arrayNode('client')->applyDefaultIfNotSet()
// 				->children()
// 					->scalarNode('pos_id')->defaultNull()->end()
// 					->scalarNode('private_key')->defaultNull()->end()
// 				->end()
// 			->end()
// 		->end();
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
