<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cops\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Algolia search service Provider
 * @author Mathieu Duplouy <mathieu.duplouy@gmail.com>
 */
class AlgoliaSearchServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Application $app)
    {
        // Algolia client
        $app['algolia-client'] = $app->share(function ($c) {

            $config = $c['config'];

            return new \AlgoliaSearch\Client(
                $config->getValue('algolia_app_id'),
                $config->getValue('algolia_api_key'),
                $config->getValue('algolia_hosts'),
                $config->getValue('algolia_options')
            );
        });

        $app['algolia-index-name'] = function($c) {
            return $c['config']->getValue('algolia_index_name').'_'.$c['config']->getValue('current_database_key');
        };

        // Algolia index
        $app['algolia'] = function ($c) {
            // Set index name on the fly to allow indexing of multiple databases
            return $c['algolia-client']->initIndex($c['algolia-index-name']);
        };

        // Settings
        $app['algolia-settings'] = array(
            'attributesToIndex' => array(
                'title',
                'authors',
                'serie',
                'tags',
                'serieIndex',
            ),
            'customRanking' => array(
                'desc(serieIndex)',
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function boot(Application $app)
    {
    }
}
