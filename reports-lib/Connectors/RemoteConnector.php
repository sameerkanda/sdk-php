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
    /** @var ProviderInterface */
    protected $service;

    /** @var string */
    protected $provider;

    /** @var array */
    protected $credentials;

    /** @var array */
    protected $lastHeaders = [];

    /** @var string */
    protected $oauthType;

    public function __construct($provider, $oauthType, ProviderInterface $providerService, 
                                array $credentials = []
    )
    {
        $this->service = $providerService;
        $this->provider = $provider;
        $this->credentials = $credentials;
        $this->oauthType = $oauthType;
        
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
        $options = [
            'path' => $path,
            'options' => $options,
            'headers' => $headers
        ];
        
        $options = array_merge($options, $this->credentials);
        $response = $this->service->request($this->provider, 'get', $options);
        
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
        $options = [
            'url' => $url,
            'options' => $options,
            'headers' => $headers
        ];

        $options = array_merge($options, $this->credentials);
        $response = $this->service->request($this->provider, 'get', $options);
        
        $this->setLastHeaders($response->headers);

        return $response->response;
    }

    /**
     * @return array
     */
    public function getLastHeaders()
    {
        return $this->lastHeaders;
    }

    /**
     * @return string
     */
    public function getOauthType()
    {
        return $this->oauthType;
    }

    public function __toString()
    {
        return $this->provider;
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