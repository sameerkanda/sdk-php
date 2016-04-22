<?php

/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>.
 */

namespace RAM\Interfaces;

/**
 * Description of ConnectorInterface.
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
interface ConnectorInterface
{
    /**
     * ConnectorInterface constructor.
     * 
     * @param string $provider
     * @param string $oauthType
     * @param ProviderInterface $providerService
     * @param array $credentials
     */
    public function __construct($provider, $oauthType, ProviderInterface $providerService,
                                array $credentials = []
    );
    
    /**
     * Get an API call response from path.
     *
     * @param string $path          The requested API path
     * @param array $options        Extra options for the requested path
     * @param array $headers        Custom request headers
     * @param bool $array           Return the results as an associative array
     * @param bool $useProxy        Use proxy for the results
     * @param bool $permanent       Persist permanent the call to proxy (Never update)
     * @param bool $force           Force update of proxy record for the call
     *
     * @return \Buzz\Message\MessageInterface
     */
    public function get($path, array $options = [], array $headers = [],
                        $array = false, $useProxy = true, $permanent = false,
                        $force = false);

    /**
     * Get an API call response from url.
     *
     * @param string $url           The requested API url
     * @param array $options        Extra options for the requested path
     * @param array $headers        Custom request headers
     * @param bool $array           Return the results as an associative array
     * @param bool $useProxy        Use proxy for the results
     * @param bool $permanent       Persist permanent the call to proxy (Never update)
     * @param bool $force           Force update of proxy record for the call
     *
     * @return \Buzz\Message\MessageInterface
     */
    public function getAbsolute($url, array $options = [], array $headers = [],
                        $array = false, $useProxy = true, $permanent = false,
                        $force = false);

    /**
     * Get last call response headers
     * 
     * @return array
     */
    public function getLastHeaders();

    /**
     * Get oauth type {"oauth1"|"oauth2"}
     * 
     * @return string
     */
    public function getOauthType();
    
    public function __toString();
}
