<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 14.04.15
 * Time: 9:14
 */

// Composer
require('../vendor/autoload.php');


$storage = new \afinogen89\getmail\storage\File('../eml/');

//// Вывод одного письма
//$msg = $storage->getMessage(167); 
//
//$msg->saveToFile('1.eml');
//
//foreach ($msg->getParts() as $part) {
//    echo  $part->getContentDecode().PHP_EOL;
//}
//echo PHP_EOL.'Count Files: '.count($msg->getAttachments()).PHP_EOL;
//
//foreach ($msg->getAttachments() as $attachment) {
//    echo $attachment->filename.PHP_EOL;
//}
//
//exit;
//Перебор всех писем
//echo $storage->countMessage().PHP_EOL;

for($i=0; $i< $storage->countMessage(); $i++) {
    echo PHP_EOL.'---------------------------------------- '.$i.' ------------------------------------------------'.PHP_EOL;
    
    $msg = $storage->getMessage($i);

    echo $msg->getHeaders()->getFrom().PHP_EOL;
    echo $msg->getHeaders()->getFromName().PHP_EOL;
    echo $msg->getHeaders()->getTo().PHP_EOL;
    echo $msg->getHeaders()->getCC().PHP_EOL;
    
    echo $msg->getHeaders()->getSubject().PHP_EOL;

    foreach ($msg->getParts() as $part) {
        echo $part->getContentDecode();
    }

    echo PHP_EOL.'Count Files: '.count($msg->getAttachments()).PHP_EOL;
    foreach ($msg->getAttachments() as $attachment) {
        echo $attachment->filename.PHP_EOL;
    }
}