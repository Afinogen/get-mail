<?php

namespace afinogen89\getmail\protocol;

/**
 * Class Imap
 * @package afinogen89\getmail\protocol
 */
class Imap
{
    /** @var string  */
    private $_host;
    /** @var int  */
    private $_port;
    /** @var  resource */
    private $_stream;
    /** @var string  */
    private $_protocol;
    /** @var string  */
    private $_box;

    /**
     * @param string $host
     * @param string $port
     * @param null $protocol
     */
    public function __construct($host, $port, $protocol = null)
    {
        $this->_host = $host;
        $this->_port = $port;

        if ($port == 143) {
            $this->_box = 'INBOX';
        } elseif ($port == 110) {
            $this->_protocol = '/pop3';
            $this->_box = 'INBOX';
        } elseif ($port == 993) {
            $this->_protocol = '/imap/ssl';
            $this->_box = 'INBOX';
        } elseif ($port == 995) {
            $this->_protocol = '/pop3/ssl/novalidate-cer';
        } elseif ($port == 119) {
            $this->_protocol = '/nntp';
        } else {
            new \Exception('Порт не поддерживается');
        }

        if ($protocol != null) {
            $this->_protocol = $protocol;
        }
    }

    /**
     * Авторизация на сервере
     * @param string $login
     * @param string $password
     */
    public function login($login, $password)
    {
        $this->_stream = imap_open('{'.$this->_host.':'.$this->_port.$this->_protocol.'}'.$this->_box, $login, $password);
        if ($this->_stream == false) {
            new \Exception('Невозможно подключиться');
        }
    }

    /**
     * Закрытие протокола
     */
    public function close()
    {
        imap_close($this->_stream);
    }

    /**
     * Получение кол-ва сообщений на сервере
     * @return int
     */
    public function countMessage()
    {
        return imap_num_msg($this->_stream);
    }

    /**
     * Получение заголовков писем
     * @param null|int $id
     * @return array|object
     */
    public function getHeader($id = null)
    {
        if (!is_null($id)) {
            return imap_fetchheader($this->_stream, $id);
        } else {
            return imap_headers($this->_stream);
        }
    }

    /**
     * @param int $id
     * @return object
     */
    public function getStructure($id)
    {
        return imap_fetchstructure($this->_stream, $id);
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
