<?php

namespace afinogen89\getmail\protocol;

/**
 * Class Pop3
 *
 * @package protocol
 */
class Pop3
{
    /** @var int */
    public $timeoutConnection = 30;

    private $hasTop;

    /** @var */
    private $_socket;

    /** @var  int */
    private $_timestamp;

    /**
     * @param string $host
     * @param null|int $port
     * @param bool $ssl
     */
    public function __construct($host = '', $port = null, $ssl = false)
    {
        if ($host) {
            $this->connect($host, $port, $ssl);
        }
    }

    /**
     * Public destructor
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Open connection to POP3 server
     *
     * @param  string $host hostname or IP address of POP3 server
     * @param  int|null $port of POP3 server, default is 110 (995 for ssl)
     * @param  string|bool $ssl use 'SSL', 'TLS' or false
     *
     * @throws \RuntimeException
     * @return string welcome message
     */
    public function connect($host, $port = null, $ssl = false)
    {
        $isTls = false;

        if ($ssl) {
            $ssl = strtolower($ssl);
        }

        switch ($ssl) {
            case 'ssl':
                $host = 'ssl://'.$host;
                if (!$port) {
                    $port = 995;
                }
                break;
            case 'tls':
                $isTls = true;
            // break intentionally omitted
            default:
                if (!$port) {
                    $port = 110;
                }
        }

        try {
            $this->_socket = fsockopen($host, $port, $errno, $errstr, $this->timeoutConnection);
        } catch (\Exception $e) {
            //throw new \RuntimeException('cannot connect to host '.$host.PHP_EOL.$errstr, $errno);
        }

        if (!$this->_socket) {
            throw new \RuntimeException($e->getMessage());
        }

        $welcome = $this->readResponse();

        strtok($welcome, '<');
        $this->_timestamp = strtok('>');
        if (!strpos($this->_timestamp, '@')) {
            $this->_timestamp = null;
        } else {
            $this->_timestamp = '<'.$this->_timestamp.'>';
        }

        if ($isTls) {
            $this->request('STLS');
            $result = stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$result) {
                throw new \RuntimeException('cannot enable TLS');
            }
        }

        return $welcome;
    }

    /**
     * Send a request
     *
     * @param string $request your request without newline
     *
     * @throws \RuntimeException
     */
    public function sendRequest($request)
    {
        try {
            $result = fputs($this->_socket, $request."\r\n");
        } catch (\Exception $e) {

        }

        if (!$result) {
            throw new \RuntimeException('send failed - connection closed?'.$e->getMessage());
        }
    }

    /**
     * read a response
     *
     * @param  bool $multiline response has multiple lines and should be read until "<nl>.<nl>"
     *
     * @throws \RuntimeException
     * @return string response
     */
    public function readResponse($multiline = false)
    {
        try {
            $result = fgets($this->_socket);
        } catch (\Exception $e) {

        }

        if (!is_string($result)) {
            throw new \RuntimeException('read failed - connection closed?'.$e->getMessage());
        }

        $result = trim($result);
        if (strpos($result, ' ')) {
            list($status, $message) = explode(' ', $result, 2);
        } else {
            $status = $result;
            $message = '';
        }

        if ($status != '+OK') {
            throw new \RuntimeException($result);
        }

        if ($multiline) {
            $message = '';
            $line = fgets($this->_socket);
            while ($line && rtrim($line, "\r\n") != '.') {
                if ($line[0] == '.') {
                    $line = substr($line, 1);
                }
                $message .= $line;
                $line = fgets($this->_socket);
            };
        }

        return $message;
    }

    /**
     * Send request and get response
     *
     * @see sendRequest()
     * @see readResponse()
     *
     * @param  string $request request
     * @param  bool $multiline multiline response?
     *
     * @return string             result from readResponse()
     */
    public function request($request, $multiline = false)
    {
        $this->sendRequest($request);
        return $this->readResponse($multiline);
    }

    /**
     * End communication with POP3 server (also closes socket)
     */
    public function logout()
    {
        if ($this->_socket) {
            try {
                $this->request('QUIT');
            } catch (\Exception $e) {
                // ignore error - we're closing the socket anyway
            }

            fclose($this->_socket);
            $this->_socket = null;
        }
    }


    /**
     * Get capabilities from POP3 server
     *
     * @return array list of capabilities
     */
    public function capa()
    {
        $result = $this->request('CAPA', true);
        return explode("\n", $result);
    }


    /**
     * Login to POP3 server. Can use APOP
     *
     * @param  string $user username
     * @param  string $password password
     * @param  bool $tryApop should APOP be tried?
     */
    public function login($user, $password, $tryApop = true)
    {
        if ($tryApop && $this->_timestamp) {
            try {
                $this->request("APOP $user ".md5($this->_timestamp.$password));
                return;
            } catch (\Exception $e) {
                // ignore
            }
        }

        $this->request("USER $user");
        $this->request("PASS $password");
    }


    /**
     * Make STAT call for message count and size sum
     *
     * @param  int $messages out parameter with count of messages
     * @param  int $octets out parameter with size in octets of messages
     */
    public function status(&$messages, &$octets)
    {
        $messages = 0;
        $octets = 0;
        $result = $this->request('STAT');

        list($messages, $octets) = explode(' ', $result);
    }


    /**
     * Make LIST call for size of message(s)
     *
     * @param  int|null $msgno number of message, null for all
     *
     * @return int|array size of given message or list with array(num => size)
     */
    public function getList($msgno = null)
    {
        if ($msgno !== null) {
            $result = $this->request("LIST $msgno");

            list(, $result) = explode(' ', $result);
            return (int)$result;
        }

        $result = $this->request('LIST', true);
        $messages = [];
        $line = strtok($result, "\n");
        while ($line) {
            list($no, $size) = explode(' ', trim($line));
            $messages[(int)$no] = (int)$size;
            $line = strtok("\n");
        }

        return $messages;
    }


    /**
     * Make UIDL call for getting a uniqueid
     *
     * @param  int|null $msgno number of message, null for all
     *
     * @return string|array uniqueid of message or list with array(num => uniqueid)
     */
    public function uniqueid($msgno = null)
    {
        if ($msgno !== null) {
            $result = $this->request("UIDL $msgno");

            list(, $result) = explode(' ', $result);
            return $result;
        }

        $result = $this->request('UIDL', true);

        $result = explode("\n", $result);
        $messages = [];
        foreach ($result as $line) {
            if (!$line) {
                continue;
            }
            list($no, $id) = explode(' ', trim($line), 2);
            $messages[(int)$no] = $id;
        }

        return $messages;
    }


    /**
     * Make TOP call for getting headers and maybe some body lines
     * This method also sets hasTop - before it it's not known if top is supported
     * The fallback makes normal RETR call, which retrieves the whole message. Additional
     * lines are not removed.
     *
     * @param  int $msgno number of message
     * @param  int $lines number of wanted body lines (empty line is inserted after header lines)
     * @param  bool $fallback fallback with full retrieve if top is not supported
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @return string message headers with wanted body lines
     */
    public function top($msgno, $lines = 0, $fallback = false)
    {
        if ($this->hasTop === false) {
            if ($fallback) {
                return $this->retrieve($msgno);
            } else {
                throw new \RuntimeException('top not supported and no fallback wanted');
            }
        }
        $this->hasTop = true;

        $lines = (!$lines || $lines < 1) ? 0 : (int)$lines;

        try {
            $result = $this->request("TOP $msgno $lines", true);
        } catch (Exception $e) {
            $this->hasTop = false;
            if ($fallback) {
                $result = $this->retrieve($msgno);
            } else {
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Make a RETR call for retrieving a full message with headers and body
     *
     * @param  int $msgno message number
     *
     * @return string message
     */
    public function retrieve($msgno)
    {
        $result = $this->request("RETR $msgno", true);
        return $result;
    }

    /**
     * Make a NOOP call, maybe needed for keeping the server happy
     */
    public function noop()
    {
        $this->request('NOOP');
    }

    /**
     * Make a DELE count to remove a message
     *
     * @param $msgno
     */
    public function delete($msgno)
    {
        $this->request("DELE $msgno");
    }

    /**
     * Make RSET call, which rollbacks delete requests
     */
    public function undelete()
    {
        $this->request('RSET');
    }
}