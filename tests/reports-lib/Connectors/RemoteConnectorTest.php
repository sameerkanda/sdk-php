<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace Tests\RAM\Connectors;
use RAM\Connectors\RemoteConnector;

/**
 * Description of RemoteConnectorTest
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class RemoteConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected $response;

    public function testGet()
    {
        $api = $this->buildMockApi();
        $this->response = new \stdClass();
        $this->response->headers = ['header1' => 'value1'];
        $this->response->response = 'response';

        $connector = new RemoteConnector('provider', 'oauth', $api);

        $response = $connector->get('path');

        $this->assertEquals('response', $response);
    }

    public function testGetAbsolute()
    {
        $api = $this->buildMockApi();
        $this->response = new \stdClass();
        $this->response->headers = ['header1' => 'value1'];
        $this->response->response = 'response';

        $connector = new RemoteConnector('provider', 'oauth', $api);

        $response = $connector->getAbsolute('url');

        $this->assertEquals('response', $response);
    }

    public function testLastHeaders()
    {
        $api = $this->buildMockApi();
        $this->response = new \stdClass();
        $this->response->headers = ['header1' => 'value1'];
        $this->response->response = 'response';

        $connector = new RemoteConnector('provider', 'oauth', $api);

        $response = $connector->getAbsolute('url');

        $this->assertEquals(['header1' => 'value1'], $connector->getLastHeaders());

        $this->response->headers = new \stdClass();
        $this->response->headers->header2 = 'value2';

        $response = $connector->getAbsolute('url');

        $this->assertEquals(['header2' => 'value2'], $connector->getLastHeaders());
    }

    public function testOauthType()
    {
        $api = $this->buildMockApi();
        $connector = new RemoteConnector('provider', 'oauth', $api);

        $this->assertEquals('oauth', $connector->getOauthType());
    }

    public function testString()
    {
        $api = $this->buildMockApi();
        $connector = new RemoteConnector('provider', 'oauth', $api);

        $this->assertEquals('provider', (string)$connector);
    }

    protected function buildMockApi()
    {
        $api = $this->getMockBuilder('RAM\Interfaces\ProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                'request'
            ])
            ->getMock();

        $api->expects($this->any())
            ->method('request')
            ->willReturnCallback(function() {
                return $this->response;
            });

        return $api;
    }
}