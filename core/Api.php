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
use RG\Traits\MockConnectorTrait;

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
        $schema = isset($params['schema']) ? $params['schema'] : 'https';
        $host = $params['host'];
        $port = isset($params['port']) ? ":{$params['port']}" : '';
        $version = isset($params['version']) ? $params['version'] : null;
        
        $this->api = "$schema://$host$port";
        if ($version) {
            $this->api .= "/$version";
        }
    }

    /**
     * Get list of available providers
     * 
     * @return array
     */
    public function getAvailableProviders()
    {
        return $this->getFromApi('available_connectors', [], true);
    }
    
    /**
     * Get specific provider
     * 
     * @param string $provider
     *
     * @return mixed
     */
    public function getProvider($provider)
    {
        $availableProviders = $this->getAvailableProviders();

        if (!isset($availableProviders[$provider])) {
            throw new ConnectorNotFoundException($provider);
        }

        $connector = $availableProviders[$provider];
        $connector['provider'] = $provider;
        
        return $connector;
    }

    /**
     * Get data from a specific path of a provider
     *
     * @param string $provider
     * @param string $path
     * @param array $options
     *
     * @return mixed
     */
    public function request($provider, $path, array $options = []
    )
    {
        $path = MockConnectorTrait::sanitizePath($path);

        return $this->getFromApi("connector/$provider/$path", $options);
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
    protected function getFromApi($path, array $options = [], $array = false)
    {
        $path = MockConnectorTrait::sanitizePath($path);
        $baseUrl = "$this->api/$path";

        $url = MockConnectorTrait::bindUrlOptions($baseUrl, $options);

        try {
            $response = $this->client->get($url);
        } catch (RequestException $requestException) {
            $response = $requestException->getResponse();
        }

        if (!$response) {
            throw new ApiException($url, 'Empty result', 500);
        }
        $content = json_decode($response->getBody()->getContents());

        if (isset($content->errors)) {
            throw new ApiException($url, $content->errors->message, $content->errors->code);
        }

        if ($array) {
            return json_decode(json_encode($content->data), true);
        }
        return $content->data;
    }
}