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
    public const FILE = 'File';
    public const POP3 = 'Pop3';
    public const IMAP = 'Imap';

    /**
     * @param array $config
     *
     * @return StorageInterface
     */
    public static function init(array $config)
    {
        $allowedStorages = [
            self::FILE,
            self::IMAP,
            self::POP3
        ];

        if (empty($config['storage']) || !in_array($config['storage'], $allowedStorages)) {
            throw new \InvalidArgumentException('need at least type in params');
        }

        $className = __NAMESPACE__.'\\'.$config['storage'];

        return new $className($config);
    }
}