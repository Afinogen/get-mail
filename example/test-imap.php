<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 17.04.15
 * Time: 23:21
 */

// Composer
require('../vendor/autoload.php');

$protocol = new \afinogen89\getmail\protocol\Imap('imap.gmail.com', 993);
$protocol->login('afinogen89@gmail.com', 'dksdjrcbfijvnfeo');
var_dump($protocol->getHeader(1));
var_dump($protocol->getStructure(3));
$protocol->close();