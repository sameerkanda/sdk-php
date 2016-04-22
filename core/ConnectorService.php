<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG;
use RAM\Connectors\MockRemoteConnector;
use RAM\Connectors\RemoteConnector;
use RAM\Interfaces\ConnectorInterface;

/**
 * Description of ConnectorService
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class ConnectorService
{
    protected $connectors = [];

    protected $client;

    protected $proxy;

    protected $connection;

    protected $responses;

    public function __construct($connectors, Api $service, Proxy $proxy,
                                $connection = 'live', array $responses = [])
    {
        $this->service = $service;
        $this->proxy = $proxy;
        $this->connection = $connection;
        $this->responses = $responses;
        foreach($connectors as $connector => $params) {
            $this->connectors[$connector] = $this->buildConnector($connector, $params);
        }
    }

    /**
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $connection
     * 
     * @return ConnectorService
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        
        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableConnectors()
    {
        $connectors = $this->service->getAvailableProviders();

        uasort($connectors, function($e1, $e2) {
            return $e1['name'] > $e2['name'];
        });

        return $connectors;
    }

    /**
     * @return array
     */
    public function getConnectors()
    {
        return $this->connectors;
    }

    /**
     * @return ConnectorInterface
     */
    public function buildOpenConnector()
    {
        return $this->buildConnector('open');
    }

    /**
     * @param string $provider
     * @param array $params
     *
     * @return ConnectorInterface
     */
    public function buildConnector($provider, array $params = [])
    {
        $connector = $this->service->getProvider($provider);
        if ($this->connection === 'sandbox') {
            $connector = new MockRemoteConnector($connector['provider'], $connector['oauth'], $this->service, $params);
            if (!isset($this->responses[$provider])) {
                throw new \RuntimeException("You requested sandbox environment for '$provider' connector, but you haven't defined any responses in app/config/responses.yml");
            }
            $connector->setResponses($this->responses[$provider]);
        } else if ($this->connection === 'live') {
            $connector = new RemoteConnector($connector['provider'], $connector['oauth'], $this->service, $params);
        } else {
            throw new \RuntimeException("No valid connection type was found. Valid types are 'sandbox' or 'live'");
        }

        return $connector;
    }

    /**
     * @param ConnectorInterface $connector
     * @param array $params
     *
     * @return mixed
     */
    public function requestToken(ConnectorInterface $connector, array $params = [])
    {
        return $this->service->request((string)$connector, 'token/request', $params);
    }

    /**
     * @param ConnectorInterface $connector
     * @param array $params
     * 
     * @return mixed
     */
    public function authorizeToken(ConnectorInterface $connector, array $params = [])
    {
        return $this->service->request((string)$connector, 'token/authorize', $params);
    }
}