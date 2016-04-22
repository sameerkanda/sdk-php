<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG\Composer;

use Composer\Script\Event;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of ScriptHandler
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class ScriptHandler
{
    public static $neededFolders = [
        'src',
        'web/assets',
        'storage',
        'proxy'
    ];

    /**
     * Create all needed folders
     *
     * @param Event $event
     */
    public static function createFolders(Event $event)
    {
        foreach(self::$neededFolders as $folder) {
            if (!is_dir($folder)) {
                umask(0);
                mkdir($folder, 0777, true);
            }
        }
    }

    /**
     * Setup config
     *
     * @param Event $event
     */
    public static function setupConfig(Event $event)
    {
        $distYml = 'app/config/config.yml.dist';
        $distConfig = Yaml::parse($distYml);

        $configYml = 'app/config/config.yml';
        if (file_exists($configYml)) {
            $config = Yaml::parse($configYml);

            $distConfig = self::updateConfig($distConfig, $config);
        }

        file_put_contents($configYml, Yaml::dump($distConfig, 4));

        $connectorsYml = 'app/config/connectors.yml';
        $responsesYml = 'app/config/responses.yml';
        if (!file_exists($connectorsYml)) {
            file_put_contents($connectorsYml, file_get_contents($connectorsYml.'.dist'));
        }
        if (!file_exists($responsesYml)) {
            file_put_contents($responsesYml, file_get_contents($responsesYml.'.dist'));
        }
        return;
    }

    /**
     * Update missing values from config file
     *
     * @param array $source
     * @param array $dest
     *
     * @return array
     */
    protected static function updateConfig(array $source, array $dest)
    {
        foreach ($source as $key => $value) {
            if (!isset($dest[$key])) {
                $dest[$key] = $value;
            } else {
                if (is_array($value)) {
                    $subDiff = self::updateConfig($value, $dest[$key]);
                    if (count($subDiff)) {
                        $dest[$key] = $subDiff;
                    }
                }
            }
        }

        return $dest;
    }
}