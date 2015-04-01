Get Mail from POP3
==================

Класс для получение писем с почтового сервера по протоколу pop3  
Часть работы с протоколом было выдернута из zf2, остальное самописное.

Изначально писалось для yii2, но можно использовать и без него.

Установка
------------

Установка происходит через composer

```
php composer.phar require --prefer-dist afinogen89/get-mail "dev-master"
```

или добавлением

```
"afinogen89/get-mail": "*"
```

в файл `composer.json` .

Использование
------------

Работа только с протоколом:

```php
$pop3 = new protocol\Pop3('example.ru');
$pop3->login('data@example.ru', '123456');
$msgList = $pop3->getList();
$pop3->logout();
```

Работа с письмами:

```php
$storage = new storage\Pop3(['host' => 'example.ru', 'user' => 'data@example.ru', 'password' => '123456']);
$msg = $storage->getMessage(1);
$msg->saveToFile('/tmp/1.eml');
echo $msg->getHeader()->getSubject();

foreach($msg->getParts() as $part) {
    echo $part->getContentDecode().PHP_EOL;
}

foreach($msg->getAttachment() as $t) {
    $t->saveToFile('/tmp/' . $t->filename);
}
```

English version
-----------

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist afinogen89/get-mail "dev-master"
```

or add

```
"afinogen89/get-mail": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  POP3 protocol:

```php
$pop3 = new protocol\Pop3('example.ru');
$pop3->login('data@example.ru', '123456');
$msgList = $pop3->getList();
$pop3->logout();
```

Get message from mail:

```php
$storage = new storage\Pop3(['host' => 'example.ru', 'user' => 'data@example.ru', 'password' => '123456']);
$msg = $storage->getMessage(1);
$msg->saveToFile('/tmp/1.eml');
echo $msg->getHeader()->getSubject();

foreach($msg->getParts() as $part) {
    echo $part->getContentDecode().PHP_EOL;
}

foreach($msg->getAttachment() as $t) {
    $t->saveToFile('/tmp/' . $t->filename);
}
```