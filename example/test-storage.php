<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 08.09.16
 * Time: 14:03
 */

// Composer
require('../vendor/autoload.php');

$storage = \afinogen89\getmail\storage\Storage::init(
    [
        'storage' => \afinogen89\getmail\storage\Storage::POP3,
        'host' => 'pop.gmail.com',
        'user' => 'test@gmail.com',
        'password' => '123456',
        'ssl' => 'SSL'
    ]
);

echo $storage->countMessages().PHP_EOL;

// Вывод одного письма
$msg = $storage->getMessage(1);

echo $msg->getMsgBody();
echo PHP_EOL.PHP_EOL;
echo $msg->getMsgAlternativeBody();