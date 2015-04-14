<?php

namespace afinogen89\getmail\storage;

use afinogen89\getmail\protocol;

class File
{
    /** @var  protocol\File */
    private $_protocol;

    public function __construct($path)
    {
        $this->_protocol = new protocol\File($path);
    }

    /**
     * @return int
     */
    public function countMessage()
    {
        return $this->_protocol->countMessage();
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
     * @param int $id
     */
    public function removeMessage($id)
    {
        $this->_protocol->delete($id);
    }

    /**
     * Получение сообщения
     * @param int $id
     * @return Message
     */
    public function getMessage($id)
    {
        $header = $this->_protocol->top($id);
        $message = $this->_protocol->retrieve($id);

        return new Message($header, $message, $id);
    }
}