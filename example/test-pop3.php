<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 16.04.15
 * Time: 15:16
 */

// Composer
require('../vendor/autoload.php');

$storage = new \afinogen89\getmail\storage\Pop3(['host' => 'pop.gmail.com', 'user' => 'test@gmail.com', 'password' => 'pass', 'ssl' => 'SSL']);

echo $storage->countMessages().PHP_EOL;

// Вывод одного письма
$msg = $storage->getMessage(1);

$msg->saveToFile('1.eml');

echo 'Count Files: '.count($msg->getAttachment());

foreach ($msg->getParts() as $part) {
    echo  $part->getContentDecode().PHP_EOL;
}