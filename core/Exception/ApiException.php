<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG\Exception;

/**
 * Description of ApiException
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class ApiException extends \RuntimeException
{
    public function __construct($url, $message, $code, \Exception $previous = null)
    {
        parent::__construct("$message ($url)", $code, $previous);
    }
}