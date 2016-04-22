<?php

/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>.
 */

namespace RG\Traits;
use RAM\Connectors\MockRemoteConnector;

/**
 * Description of MockConnectorTrait.
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
trait MockConnectorTrait
{
    protected $responses;

    /**
     * Sanitize path before passing it to url building
     *
     * @param string $path
     *
     * @return string
     */
    public static function sanitizePath($path)
    {
        return ltrim($path, '/ ');
    }

    /**
     * Append query to url if any options are defined
     *
     * @param string $url
     * @param array $options
     *
     * @return string
     */
    public static function bindUrlOptions($url, array $options = [])
    {
        $urlParts = parse_url($url);
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $existingOptions);
            foreach($options as $key => $value) {
                $existingOptions[$key] = $value;
            }
            $options = $existingOptions;
            $url = $urlParts['scheme'].'://'.$urlParts['host'].$urlParts['path'];
        }
        $query = http_build_query($options);
        if ($query !== '') {
            $url .= "?$query";
        }

        return $url;
    }

    /**
     * @param mixed $responses
     *
     * @return MockRemoteConnector
     */
    public function setResponses($responses)
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * @param string $path
     * @param array $options
     * @param array $headers
     * @param bool $array
     *
     * @return \stdClass|array
     */
    public function get($path, array $options = [], array $headers = [],
                        $array = false, $useProxy = true, $permanent = false,
                        $force = false)
    {
        $path = self::sanitizePath($path);
        $query = http_build_query($options);
        if ($query !== '') {
            $path .= "?$query";
        }
        if (isset($this->responses[$path])) {
            return $this->responses[$path];
        }
        $response = new \stdClass();
        $response->status = 'error';
        $response->message = "No mock route '$path' found in app/config/responses.yml";

        return json_decode(json_encode($response), $array);
    }

    /**
     * @param string $url
     * @param array $options
     * @param array $headers
     * @param bool $array
     *
     * @return \stdClass|array
     */
    public function getAbsolute($url, array $options = [], array $headers = [],
                                $array = false, $useProxy = true, $permanent = false,
                                $force = false)
    {
        $url = self::bindUrlOptions($url, $options);
        if (isset($this->responses[$url])) {
            return $this->responses[$url];
        }
        $response = new \stdClass();
        $response->status = 'error';
        $response->message = "No mock route '$url' found in app/config/responses.yml";

        return json_decode(json_encode($response), $array);
    }
}
