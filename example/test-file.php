<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 14.04.15
 * Time: 9:14
 */

// Composer
require('../vendor/autoload.php');


$storage = new \afinogen89\getmail\storage\File(['path' => '../eml/']);

//// Вывод одного письма
//$msg = $storage->getMessage(1357); 
//
//$msg->saveToFile('1.eml');
//
//echo 'From: '.$msg->getHeaders()->getFrom().PHP_EOL;
//echo 'From Name: '.$msg->getHeaders()->getFromName().PHP_EOL;
//echo 'To: '.$msg->getHeaders()->getTo().PHP_EOL;
//echo 'Copy: '.$msg->getHeaders()->getCC().PHP_EOL;
//
//echo 'Subject: '.$msg->getHeaders()->getSubject().PHP_EOL;
//
//foreach ($msg->getParts() as $part) {
//    echo $part->getContentDecode();
//}
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

for ($i = 0; $i < $storage->countMessages(); $i++) {
    echo PHP_EOL.'---------------------------------------- '.$i.' ------------------------------------------------'.PHP_EOL;

    $msg = $storage->getMessage($i);

    echo 'From: '.$msg->getHeaders()->getFrom().PHP_EOL;
    echo 'From Name: '.$msg->getHeaders()->getFromName().PHP_EOL;
    echo 'To: '.$msg->getHeaders()->getTo().PHP_EOL;
    echo 'Copy: '.$msg->getHeaders()->getCC().PHP_EOL;

    echo 'Subject: '.$msg->getHeaders()->getSubject().PHP_EOL;

    foreach ($msg->getParts() as $part) {
        echo $part->getContentDecode();
    }

    echo PHP_EOL.'Count Files: '.count($msg->getAttachments()).PHP_EOL;
    foreach ($msg->getAttachments() as $attachment) {
        echo $attachment->filename.PHP_EOL;
    }
}