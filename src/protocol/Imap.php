<?php

namespace afinogen89\getmail\protocol;

use afinogen89\getmail\protocol\Exception\ImapException;

/**
 * Class Imap
 *
 * @package afinogen89\getmail\protocol
 */
class Imap
{
    /** @var string */
    private $_host;

    /** @var int */
    private $_port;

    /** @var  resource */
    private $_stream;

    /** @var string */
    private $_protocol;

    /** @var string */
    private $_box;

    /**
     * Imap constructor.
     *
     * @param string      $host
     * @param int         $port
     * @param string|null $protocol
     */
    public function __construct(string $host, int $port, ?string $protocol = null)
    {
        $this->_host = $host;
        $this->_port = $port;

        if ($port == 143) {
            $this->_box = 'INBOX';
        } elseif ($port == 110) {
            $this->_protocol = '/pop3';
            $this->_box      = 'INBOX';
        } elseif ($port == 993) {
            $this->_protocol = '/imap/ssl';
            $this->_box      = 'INBOX';
        } elseif ($port == 995) {
            $this->_protocol = '/pop3/ssl/novalidate-cer';
        } elseif ($port == 119) {
            $this->_protocol = '/nntp';
        } else {
            throw new ImapException('Порт не поддерживается');
        }

        if ($protocol != null) {
            $this->_protocol = $protocol;
        }
    }

    /**
     * Заглушка
     *
     * @param string      $host
     * @param int|null  $port
     * @param false $ssl
     */
    public function connect($host, $port = null, $ssl = false)
    {

    }

    /**
     * Авторизация на сервере
     *
     * @param string $login
     * @param string $password
     */
    public function login(string $login, string $password): void
    {
        $this->_stream = imap_open('{'.$this->_host.':'.$this->_port.$this->_protocol.'}'.$this->_box, $login, $password);
        if ($this->_stream == false) {
            throw new ImapException('Невозможно подключиться');
        }
    }

    /**
     * Закрытие протокола
     */
    public function close(): void
    {
        imap_close($this->_stream);
    }

    /**
     * Получение кол-ва сообщений на сервере
     *
     * @return int
     */
    public function countMessages(): int
    {
        return imap_num_msg($this->_stream);
    }

    /**
     * Получение заголовков писем
     *
     * @param int $id
     *
     * @return string
     */
    public function getHeader($id)
    {
        return imap_fetchheader($this->_stream, $id);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getBody($id)
    {
        return imap_body($this->_stream, $id, FT_INTERNAL);
    }

    /**
     * @param int $id
     *
     * @return object
     */
    public function getStructure($id)
    {
        return imap_fetchstructure($this->_stream, $id);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return imap_delete($this->_stream, $id);
    }

    /**
     * @return array
     */
    public function getAlerts()
    {
        return imap_alerts();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return imap_errors();
    }

    /**
     * @return string
     */
    public function getLastError()
    {
        return imap_last_error();
    }
}
