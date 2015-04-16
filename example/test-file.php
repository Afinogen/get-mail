<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 14.04.15
 * Time: 9:14
 */

// Composer
require('../vendor/autoload.php');


$storage = new \afinogen89\getmail\storage\File('/home/afinogen/dumps/email/');

/// Вывод одного письма
//$msg = $storage->getMessage(658);
//
//$msg->saveToFile('1.eml');
//
//echo 'Count Files: '.count($msg->getAttachment());
//
//foreach ($msg->getParts() as $part) {
//    echo  $part->getContentDecode().PHP_EOL;
//}

//Перебор всех писем
//echo $storage->countMessage().PHP_EOL;

for($i=0; $i< $storage->countMessage(); $i++) {
    echo PHP_EOL.'---------------------------------------- '.$i.' ------------------------------------------------'.PHP_EOL;

    $msg = $storage->getMessage($i);

    echo $msg->getHeader()->getSubject() . PHP_EOL;

    foreach ($msg->getParts() as $part) {
            echo $part->getContentDecode();
    }

    echo 'Count Files: '.count($msg->getAttachment());
}