<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RAM\Interfaces;
use GuzzleHttp\ClientInterface;

/**
 * Description of ProviderInterface
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
interface ProviderInterface
{
    public function __construct(ClientInterface $client, array $params);
    
    public function getProvider($provider);

    /**
     * Get data from a specific path of a provider
     * 
     * @param string $provider
     * @param string $path
     * @param array $options
     * @param array $credentials
     * 
     * @return mixed
     */
    public function get($provider, $path, array $options = [], array $credentials = []);

    /**
     * Get data from a specific url of a provider
     * 
     * @param string $provider
     * @param string $path
     * @param array $options
     * @param array $credentials
     * 
     * @return mixed
     */
    public function getAbsolute($provider, $url, array $options = [], array $credentials = []);
}