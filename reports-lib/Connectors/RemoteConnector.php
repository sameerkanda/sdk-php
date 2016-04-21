<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RAM\Connectors;

use RAM\Interfaces\ConnectorInterface;
use RAM\Interfaces\ProviderInterface;

/**
 * Description of RemoteConnector
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class RemoteConnector implements ConnectorInterface
{
    protected $service;
    
    protected $provider;
    
    protected $credentials;

    protected $lastHeaders = [];

    public function __construct($provider, ProviderInterface $providerService, 
                                array $credentials = []
    )
    {
        $this->service = $providerService;
        $this->provider = $provider;
        $this->credentials = $credentials;
        
    }

    /**
     * Get an API call response from path.
     *
     * @param string $path The requested API path
     * @param array $options Extra options for the requested path
     * @param array $headers Custom request headers
     * @param bool $array Return the results as an associative array
     * @param bool $useProxy Use proxy for the results
     * @param bool $permanent Persist permanent the call to proxy (Never update)
     * @param bool $force Force update of proxy record for the call
     *
     * @return mixed
     */
    public function get($path, array $options = [], array $headers = [],
                        $array = false, $useProxy = true, $permanent = false,
                        $force = false)
    {
        $response = $this->service->get($this->provider, $path, $options, $headers, $this->credentials);
        $this->setLastHeaders($response->headers);

        return $response->response;

    }

    /**
     * Get an API call response from url.
     *
     * @param string $url The requested API url
     * @param array $options Extra options for the requested path
     * @param array $headers Custom request headers
     * @param bool $array Return the results as an associative array
     * @param bool $useProxy Use proxy for the results
     * @param bool $permanent Persist permanent the call to proxy (Never update)
     * @param bool $force Force update of proxy record for the call
     *
     * @return mixed
     */
    public function getAbsolute($url, array $options = [], array $headers = [],
                                $array = false, $useProxy = true, $permanent = false,
                                $force = false)
    {
        $response = $this->service->getAbsolute($url, $options, $headers);
        $this->setLastHeaders($response->headers);

        return $response->response;
    }

    public function getLastHeaders()
    {
        return $this->lastHeaders;
    }

    /**
     * @param mixed $headers
     *
     * @return RemoteConnector
     */
    protected function setLastHeaders($headers)
    {
        if (!is_array($headers)) {
            $headers = json_decode(json_encode($headers), true);
        }

        $this->lastHeaders = $headers;

        return $this;
    }
}