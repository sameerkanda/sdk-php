<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RAM\Interfaces;

/**
 * Description of ProviderInterface
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
interface ProviderInterface
{
    /**
     * Make a request to a provider
     * 
     * @param string $provider
     * @param string $path
     * @param array $options
     *
     * @return mixed
     */
    public function request($provider, $path, array $options = []);
}