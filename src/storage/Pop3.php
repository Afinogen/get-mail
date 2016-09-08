<?php

namespace afinogen89\getmail\storage;

use afinogen89\getmail\message\Message;
use afinogen89\getmail\protocol;

/**
 * Class Pop3
 *
 * @package storage
 */
class Pop3 implements StorageInterface
{
    /** @var protocol\Pop3 */
    private $_protocol;

    /**
     * @param array|protocol\Pop3 $params
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = (object)$params;
        }

        if ($params instanceof protocol\Pop3) {
            $this->_protocol = $params;
            return;
        }

        if (!isset($params->user)) {
            throw new \InvalidArgumentException('need at least user in params');
        }

        $host = isset($params->host) ? $params->host : 'localhost';
        $password = isset($params->password) ? $params->password : '';
        $port = isset($params->port) ? $params->port : null;
        $ssl = isset($params->ssl) ? $params->ssl : false;

        $this->_protocol = new protocol\Pop3();
        $this->_protocol->connect($host, $port, $ssl);
        $this->_protocol->login($params->user, $password);
    }

    /**
     * Count Message
     *
     * @return int
     */
    public function countMessages()
    {
        $count = 0; // "Declare" variable before first usage.
        $octets = 0; // "Declare" variable since it's passed by reference
        $this->_protocol->status($count, $octets);
        return (int)$count;
    }

    /**
     * Close resource for mail lib. If you need to control, when the resource
     * is closed. Otherwise the destructor would call this.
     */
    public function close()
    {
        $this->_protocol->logout();
    }

    /**
     * Keep the server busy.
     *
     * @throws \RuntimeException
     */
    public function noop()
    {
        $this->_protocol->noop();
    }

    /**
     * Remove a message from server. If you're doing that from a web environment
     * you should be careful and use a uniqueid as parameter if possible to
     * identify the message.
     *
     * @param  int $id number of message
     *
     * @throws \RuntimeException
     */
    public function removeMessage($id)
    {
        $this->_protocol->delete($id);
    }

    /**
     * get unique id for one or all messages
     * if storage does not support unique ids it's the same as the message number
     *
     * @param int|null $id message number
     *
     * @return array|string message number for given message or all messages as array
     * @throws \ExceptionInterface
     */
    public function getUniqueId($id = null)
    {
        if (!$this->hasUniqueid) {
            if ($id) {
                return $id;
            }
            $count = $this->countMessages();
            if ($count < 1) {
                return [];
            }
            $range = range(1, $count);
            return array_combine($range, $range);
        }

        return $this->_protocol->uniqueid($id);
    }

    /**
     * Fetch message
     *
     * @param int $id
     *
     * @return Message
     * @throws \Exception
     * @throws protocol\Exception
     */
    public function getMessage($id)
    {
        $bodyLines = 0;
        $header = $this->_protocol->top($id, $bodyLines, true);
        $message = $message = $this->getRawContent($id);
        return new Message($header, $message, $id);
    }

    /*
     * Get raw header of message or part
     *
     * @param  int               $id       number of message
     * @param  int               $topLines include this many lines with header (after an empty line)
     * @return string raw header
     * @throws \Zend\Mail\Protocol\Exception\ExceptionInterface
     * @throws \Zend\Mail\Storage\Exception\ExceptionInterface
     */
    public function getRawHeader($id, $topLines = 0)
    {
        return $this->_protocol->top($id, $topLines, true);
    }

    /*
     * Get raw content of message or part
     *
     * @param  int               $id   number of message
     * @return string raw content
     * @throws \ExceptionInterface
     * @throws \ExceptionInterface
     */
    public function getRawContent($id)
    {
        return $this->_protocol->retrieve($id);
    }
}