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

    public function getAvailableConnectors()
    {
        $connectors =  [
            'twitter' => 'Twitter',
            'facebook' => 'Facebook',
            'facebookpage' => 'Facebook Page',
            'stripe' => 'Stripe',
            'dribbble' => 'Dribbble',
            'soundcloud' => 'Soundlcoud',
            'google' => 'Google+',
            'youtubedata' => 'Youtube Data',
            'youtubeanalytics' => 'Youtube Analytics',
            'quickbooks' => 'Quickbooks',
            'fitbit' => 'Fitbit',
            'github' => 'Github',
            'fivehundredpx' => '500px',
            'angellist' => 'AngelList',
            'behance' => 'Behance',
            'box' => 'Box',
            'dropbox' => 'Dropbox',
            'etsy' => 'Etsy',
            'flickr' => 'Flickr',
            'foursquare' => 'Foursquare',
            'freshbooks' => 'FreshBooks',
            'gmail' => 'Gmail',
            'googleanalytics' => 'Google Analytics',
            'instagram' => 'Instagram',
            'pinterest' => 'Pinterest',
            'spotify' => 'Spotify',
            'linkedin' => 'LinkedIn',
            'paypal' => 'Paypal',
            'slack' => 'Slack',
            'square' => 'Square',
            'trello' => 'Trello',
            'tumblr' => 'Tumblr',
            'twitch' => 'Twitch',
            'vimeo' => 'Vimeo',
            'jawbone' => 'Jawbone',
            'vk' => 'VK',
            'weibo' => 'Weibo',
            'meetup' => 'Meetup',
            'misfit' => 'Misfit',
            'withings' => 'Withings',
            'powerbi' => 'Power BI'
        ];

        asort($connectors, SORT_STRING);

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
        return $this->buildConnector('open', []);
    }

    /**
     * @param string $provider
     * @param array $params
     *
     * @return ConnectorInterface
     */
    protected function buildConnector($provider, array $params = [])
    {
        if ($this->connection === 'sandbox') {
            $connector = new MockRemoteConnector($provider, $this->service, $params);
            if (!isset($this->responses[$provider])) {
                throw new \RuntimeException("You requested sandbox environment for '$provider' connector, but you haven't defined any responses in app/config/responses.yml");
            }
            $connector->setResponses($this->responses[$provider]);
        } else if ($this->connection === 'live') {
            $connector = new RemoteConnector($provider, $this->service, $params);
        } else {
            throw new \RuntimeException("No valid connection type was found. Valid types are 'sandbox' or 'live'");
        }
        
        return $connector;
    }
}