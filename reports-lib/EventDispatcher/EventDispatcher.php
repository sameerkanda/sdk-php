<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RAM\EventDispatcher;
use RAM\Interfaces\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Description of EventDispatcher
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class EventDispatcher extends ContainerAwareEventDispatcher implements EventDispatcherInterface
{

}