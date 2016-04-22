<?php

/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>.
 */
namespace RAM\Interfaces;

/**
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
interface ReportInterface
{
    public function __construct(EventDispatcherInterface $dispatcher, StorageInterface $storage);

    /**
     * Install process
     */
    public function install();

    /**
     * Uninstall process
     */
    public function uninstall();

    /**
     * Report render
     * @return string
     */
    public function render();

    /**
     * Get report's specific connector based on provider name
     * 
     * @param string $provider
     * 
     * @return ConnectorInterface
     */
    public function getConnector($provider);
}
