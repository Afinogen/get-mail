<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 08.09.16
 * Time: 13:57
 */

namespace afinogen89\getmail\storage;

/**
 * Class Storage
 *
 * @package afinogen89\getmail\storage
 */
class Storage
{
    const FILE = 'File';
    const POP3 = 'Pop3';

    /**
     * @param array $config
     *
     * @return StorageInterface
     */
    public static function init(array $config)
    {
        if (empty($config['storage']) || ($config['storage'] != self::FILE && $config['storage'] != self::POP3)) {
            throw new \InvalidArgumentException('need at least type in params');
        }

        $className = __NAMESPACE__.'\\'.$config['storage'];

        return new  $className($config);
    }
}