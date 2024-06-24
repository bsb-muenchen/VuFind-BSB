<?php
/**
 * Factory for SolrXml record drivers.
 *
 * PHP version 8
 *
 * Copyright (C) Bayerische Staatsbibliothek 2024.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Bayerische Staatsbibliothek
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 */

namespace Bsb\RecordDriver;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class SolrXmlFactory
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container     Service manager
     * @param string             $requestedName Service being created
     * @param null|array         $options       Extra options (optional)
     *
     * @return object
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     * creating a service.
     * @throws ContainerException&\Throwable if any other error occurs
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    )
    {
        $pluginManager = $container->get('VuFind\Config\PluginManager');
        $fidxmlConfig = $pluginManager->get('fidxml');
        $searchConfig = $pluginManager->get('searches');
        $vufindConfig = $pluginManager->get('config');

        $driver = new $requestedName($vufindConfig, $fidxmlConfig, $searchConfig);
        $driver->attachSearchService($container->get('VuFindSearch\Service'));

        return $driver;
    }
}
