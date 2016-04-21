<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RAM\Connectors;
use RG\Traits\MockConnectorTrait;

/**
 * Description of MockRemoteConnector
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class MockRemoteConnector extends RemoteConnector
{
    use MockConnectorTrait;
}