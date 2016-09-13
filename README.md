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

Протокол POP3

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

Через папку с письмами (расширение файлов eml)
```php
$storage = new storage\File(['path' => '../email/']);
$msg = $storage->getMessage(1);
$msg->saveToFile('/tmp/1.eml');
echo $msg->getHeader()->getSubject();
```

Пример подключения к gmail
```php
$storage = new storage\Pop3(['host' => 'pop.gmail.com', 'user' => 'test@gmail.com', 'password' => 'pass', 'ssl' => 'SSL']);
```

Для генерации пароля необходимо создать приложение на странице https://security.google.com/settings/security/apppasswords

Подключение нужного хранилища через конфиг, позволяет быстро переключаться не меняя код  
```php
$storage = storage\Storage::init(
    [
        'storage' => \afinogen89\getmail\storage\Storage::POP3,
        'host' => 'pop.gmail.com',
        'user' => 'test@gmail.com',
        'password' => '123456',
        'ssl' => 'SSL'
    ]
);
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