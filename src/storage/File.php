<?php

namespace afinogen89\getmail\storage;

use afinogen89\getmail\message\Message;
use afinogen89\getmail\protocol;

/**
 * Class File
 *
 * @package afinogen89\getmail\storage
 */
class File implements StorageInterface
{
    /** @var  protocol\File */
    private $_protocol;

    /**
     * File constructor.
     * Необходим параметр path для указания папки или файла с которым работать
     *
     * @param array $conf
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $conf)
    {
        if (empty($conf['path'])) {
            throw new \InvalidArgumentException('Need `path` parameters');
        }

        $this->_protocol = new protocol\File($conf['path']);
    }

    /**
     * Получение количества сообщений
     *
     * @return int
     */
    public function countMessages()
    {
        return $this->_protocol->countMessages();
    }

    /**
     * Закрытие протокола
     */
    public function close()
    {
        $this->_protocol->logout();
    }

    /**
     * Удаление сообщения
     *
     * @param int $id
     */
    public function removeMessage($id)
    {
        $this->_protocol->delete($id);
    }

    /**
     * Получение сообщения
     *
     * @param int $id
     *
     * @return Message
     */
    public function getMessage($id)
    {
        $header = $this->_protocol->top($id);
        $message = $this->_protocol->retrieve($id);

        return new Message($header, $message, $id);
    }
}