<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 14.04.15
 * Time: 9:14
 */

// Composer
require('../vendor/autoload.php');


function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
            echo "  Фатальная ошибка в строке $errline файла $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Завершение работы...<br />\n";
            exit(1);
            break;

        case E_USER_WARNING:
            echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
            break;

        default:
            echo "Неизвестная ошибка: [$errno] $errstr<br />\n";
            break;
    }

    var_dump(debug_backtrace());exit;
    /* Не запускаем внутренний обработчик ошибок PHP */
    return true;
}

//set_error_handler("myErrorHandler");

$storage = new \afinogen89\getmail\storage\File('/home/afinogen/dumps/email/');
//
//$msg = $storage->getMessage(658);
//
//$msg->saveToFile('1.eml');
//
//echo 'Count Files: '.count($msg->getAttachment());
//
//foreach ($msg->getParts() as $part) {
//    echo  $part->getContentDecode().PHP_EOL;
//}


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