<?php

/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>.
 */
namespace RG;

use Buzz\Browser;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use RG\Interfaces\ConnectorInterface;
use RG\Traits\ConnectorTrait;
use RG\Connection;
use RG\Traits\ProxyConnectorTrait;

/**
 * Description of Oauth1Connector.
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
abstract class Oauth1Connector extends Server implements ConnectorInterface
{
    protected $session;

    use ProxyConnectorTrait;
    use ConnectorTrait {
        ConnectorTrait::__construct as private __cConstruct;
    }

    public function __construct(Browser $httpClient, Proxy $proxy)
    {
        $this->__cConstruct();
        $this->client = $httpClient;
        $this->proxy = $proxy;
    }

    /**
     * {@inheritdoc}
     */
    public function buildToken(Connection $connection)
    {
        $token = new TokenCredentials();
        $token->setSecret($connection->getSecret());
        $token->setIdentifier($connection->getIdentifier());
        $this->setToken($token);
        $this->setupProvider();
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, array $options = [], array $headers = [],
                        $array = false, $useProxy = true, $permanent = false,
                        $force = false
    )
    {
        $path = self::sanitizePath($path);
        $url = $this->buildUrl($path, $options);
        $requestHeaders = $this->buildHeaders($url, $headers);
        $requestHeaders = array_merge($requestHeaders, $headers);

        if ($useProxy) {
            return $this->getFromProxy($path, $options, $requestHeaders, $array ,$permanent, $force);
        } else {
            $response = $this->client->get($url, $requestHeaders);
            $lastResponse = $this->client->getLastResponse();
            if ($lastResponse) {
                $headers = $lastResponse->getHeaders();
                $this->lastHeaders = $this->parseHeaders($headers);
            } else {
                $this->lastHeaders = [];
            }
            return self::convertContent($response->getContent(), $array);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userDetails() method.
    }

    /**
     * {@inheritdoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userUid() method.
    }

    /**
     * {@inheritdoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userEmail() method.
    }

    /**
     * {@inheritdoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userScreenName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function needsExtraParameters()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setupProvider()
    {
        parent::__construct([
            'identifier' => $this->key,
            'secret' => $this->secret,
        ]);
    }

    /**
     * Build header for the given URL.
     *
     * @param string $url
     * @param array  $extraHeaders
     *
     * @return array
     */
    protected function buildHeaders($url, array $extraHeaders = [])
    {
        $header = $this->protocolHeader('GET', $url, $this->token);
        $authorizationHeader = array('Authorization' => $header);

        $headers = $this->buildHttpClientHeaders($authorizationHeader);
        $headers['Accept'] = 'application/json';

        return array_merge($headers, $extraHeaders);
    }
}
