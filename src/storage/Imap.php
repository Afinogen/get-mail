<?php


namespace afinogen89\getmail\storage;

use afinogen89\getmail\message\Message;
use afinogen89\getmail\protocol\Imap as ImapProtocol;

/**
 * Class Imap
 *
 * @package afinogen89\getmail\storage
 */
class Imap implements StorageInterface
{
    /** @var ImapProtocol */
    private $_protocol;

    /**
     * Imap constructor.
     *
     * @param array|ImapProtocol $params
     */
    public function __construct($params)
    {
        if ($params instanceof ImapProtocol) {
            $this->_protocol = $params;
            return;
        }

        if (!isset($params['user'])) {
            throw new \InvalidArgumentException('need at least user in params');
        }

        $host = isset($params['host']) ? $params['host'] : 'localhost';
        $password = isset($params['password']) ? $params['password'] : '';
        $port = isset($params['port']) ? $params['port'] : 993;
        $ssl = isset($params['ssl']) ? $params['ssl'] : false;

        $this->_protocol = new ImapProtocol($host, $port);
        $this->_protocol->connect($host, $port, $ssl);
        $this->_protocol->login($params['user'], $password);
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
        $this->_protocol->close();
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
        $header = $this->_protocol->getHeader($id);
        $message = $this->_protocol->getBody($id);

        return new Message($header, $header.PHP_EOL.$message, $id);
    }
}