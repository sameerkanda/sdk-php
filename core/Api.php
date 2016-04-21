<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG;
use GuzzleHttp\ClientInterface;
use RAM\Exception\ConnectorNotFoundException;
use RAM\Interfaces\ProviderInterface;
use RG\Exception\ApiException;
use GuzzleHttp\Exception\RequestException;
use RG\Traits\ConnectorTrait;

/**
 * Description of Api
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class Api implements ProviderInterface
{
    protected $client;

    protected $api;

    public function __construct(ClientInterface $client, array $params)
    {
        $this->client = $client;
        
        /* Setup API */
        $schema = isset($params['schema']) ? $params['schema'] : 'http';
        $host = $params['host'];
        $port = isset($params['port']) ? $params['port'] : '80';
        $version = isset($params['version']) ? $params['version'] : null;
        
        $this->api = "$schema://$host:$port";
        if ($version) {
            $this->api .= "/$version";
        }
    }

    /**
     * @param string $provider
     *
     * @return mixed
     */
    public function getProvider($provider)
    {
        $availableProviders = $this->getFromApi('available_connectors');

        if (!isset($availableProviders->{$provider})) {
            throw new ConnectorNotFoundException($provider);
        }

        return $availableProviders->{$provider};
    }

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
    public function get($provider, $path, array $options = [], array $headers = [], 
                        array $credentials = []
    )
    {
        $options = [
            'path' => $path,
            'options' => $options,
            'headers' => $headers
        ];

        $options = array_merge($options, $credentials);

        return $this->getFromApi("connector/$provider/get", $options);
    }

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
    public function getAbsolute($provider, $url, array $options = [], array $headers = [], 
                        array $credentials = []
    )
    {
        $options = [
            'url' => $url,
            'options' => $options,
            'headers' => $headers
        ];

        $options = array_merge($options, $credentials);

        return $this->getFromApi("connector/$provider/get", $options);
    }

    /**
     * Response from API path
     * 
     * @param string $path
     * @param array $options
     * 
     * @return mixed
     */
    protected function getFromApi($path, array $options = [])
    {
        ltrim($path, "/\t\n\r\0\x0B");
        $baseUrl = "$this->api/$path";

        $url = ConnectorTrait::bindUrlOptions($baseUrl, $options);

        try {
            $response = $this->client->get($url);
        } catch (RequestException $requestException) {
            $response = $requestException->getResponse();
        }

        $content = json_decode($response->getBody()->getContents());

        if (isset($content->errors)) {
            throw new ApiException($url, $content->errors->message, $content->errors->code);
        }

        return $content->data;
    }
}