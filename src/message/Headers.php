<?php

namespace afinogen89\getmail\message;

/**
 * Class Headers
 *
 * @package storage
 */
class Headers
{
    const MULTIPART_MIXED = 'multipart/mixed';
    const MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const EMAIL_PATTERN = '#[\w\-\.\_]+@([\w\-]+\.)+[\w\-]+#si';
    const BOUNDARY_PATTERN = '/boundary\s*\=\s*["\']?([\w\=\:\-\.\/]+)/si';

    /** @var  string */
    private $_headers;
    /** @var  string */
    private $_to;
    /** @var  string */
    private $_from;
    /** @var  string */
    private $_fromName;
    /** @var  string */
    private $_cc;
    /** @var  string */
    private $_subject;
    /** @var  string */
    private $_messageContentType;
    /** @var  string */
    private $_boundary;
    /** @var  string */
    private $_date;
    /** @var  string */
    private $_charset;
    /** @var  string */
    private $_transferEncoding;
    /** @var  bool */
    private $_isAutoReply;

    /**
     * @param string $headers
     */
    public function __construct($headers)
    {
        $this->_isAutoReply = false;
        $this->_headers = $headers;
        $this->parserHeaders();
    }

    /**
     * Parser headers
     */
    protected function parserHeaders()
    {
        $headers = $this->asArray();
        $this->_to = isset($headers['to']) ? self::decodeMimeString(current($headers['to'])) : '';
        $this->_cc = isset($headers['cc']) ? self::decodeMimeString(current($headers['cc'])) : '';
        $this->_from = isset($headers['from']) ? self::decodeMimeString(current($headers['from'])) : '';
        $this->_date = isset($headers['date']) ? current($headers['date']) : '';

        preg_match(self::EMAIL_PATTERN, $this->_from, $email);
        if (!empty($email)) {
            $email = $email[0];
        } else {
            $email = null;
            throw new \Exception('Email not found in "'.$this->_from.'"');
        }

        $this->_fromName = str_replace($email, '', $this->_from);
        $this->_fromName = trim($this->_fromName, '<>,');
        $this->_fromName = trim($this->_fromName, ' "');

        $this->_from = $email;

        //TODO может быть несколько получателей
        preg_match(self::EMAIL_PATTERN, $this->_to, $email);
        if (!empty($email)) {
            $this->_to = $email[0];
        }

        $part = current($headers['content-type']);
        $this->_messageContentType = trim(explode(';', $part)[0]);

        if (preg_match_all('/(boundary|charset)\s*\=\s*["\']?([\w\-\/\=\.]+)/i', $part, $result)) {
            foreach ($result[1] as $key => $val) {
                $val = '_'.strtolower($val);
                $this->{$val} = $result[2][$key];
            }
        }

        $this->_subject = isset($headers['subject']) ? self::decodeMimeString(current($headers['subject']), $this->getCharset()) : '';

        $this->parseAutoReply();

        if (isset($headers['content-transfer-encoding'])) {
            $this->_transferEncoding = trim(current($headers['content-transfer-encoding']));
        }
    }

    public function parseAutoReply()
    {
        //TODO проверить другие варианты
        $replays = ['auto-replied'];

        $headers = $this->asArray();
        if (isset($headers['auto-submitted']) && is_array($headers['auto-submitted'])) {
            $headers['auto-submitted'] = current($headers['auto-submitted']);
        }

        if (isset($headers['x-autoreply'], $headers['auto-submitted'])) {
            if (in_array($headers['auto-submitted'], $replays)) {
                $this->_isAutoReply = true;
            }
        } elseif (isset($headers['auto-submitted']) && in_array($headers['auto-submitted'], $replays)) {
            $this->_isAutoReply = true;
        }
    }

    /**
     * @param string $strMime
     * @param string $charset
     *
     * @return string
     */
    public static function decodeMimeString($strMime, $charset = null)
    {
        $items = preg_split('/[\r\n]{2,}/si', $strMime);
        $result = '';
        foreach ($items as $item) {
            $data = explode('?', $item);
            $str = '';
            if (!empty($data) && count($data) == 1 && is_null($charset)) {
                $str = $data[0];
            } elseif (!empty($data)) {
                while (!empty($data)) {
                    $str .= self::decodeMimeStringPart($data);
                }
            }
            $result .= $str;
        }

        if (!empty($charset)){
            $strMime = mb_convert_encoding($strMime, 'UTF-8', $charset);
        }

        return $result ?: $strMime;
    }

    public static function decodeMimeStringPart(&$data)
    {
        $str = '';
        array_shift($data);
        $encode = array_shift($data);
        $type = strtoupper(array_shift($data));
        if ($type == 'B') {
            $str = base64_decode(array_shift($data));
            $str .= trim($data[0], ' =');
        } elseif ($type == 'Q') {
            $str = quoted_printable_decode(array_shift($data));
            if (!empty($data)) {
                $str .= trim($data[0], ' =');
            }
        }

        if (!empty($encode)) {
            $str = mb_convert_encoding($str, 'UTF-8', $encode);
        }

        return $str;
    }

    /**
     * @return string
     */
    public function getHeadersString()
    {
        return $this->_headers;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->_fromName;
    }

    /**
     * @return string
     */
    public function getCC()
    {
        return $this->_cc;
    }

    /**
     * @return string
     */
    public function getMessageContentType()
    {
        return $this->_messageContentType;
    }

    /**
     * @return string
     */
    public function getMessageBoundary()
    {
        return $this->_boundary;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * @return string
     */
    public function getTransferEncoding()
    {
        return $this->_transferEncoding;
    }

    /**
     * @return bool
     */
    public function isAutoReply()
    {
        return $this->_isAutoReply;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return self::toArray($this->_headers);
    }

    /**
     * @param string $headers
     *
     * @return array
     */
    public static function toArray($headers)
    {
        $headers .= "\r\n\r\n";
        preg_match_all('#[\r\n]*([\w-]+\:)(.+?)(?=([\r\n]+[\w-]+\:|[\r\n]{3,}|\n{2,}))#si', $headers, $result);

        $headers = [];
        foreach ($result[1] as $k => $header) {
            $header = strtolower(rtrim($header, ':'));
            $headers[$header][] = trim($result[2][$k]);
        }

        return $headers;
    }
}
