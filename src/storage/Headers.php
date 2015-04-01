<?php

namespace afinogen89\getmail\storage;

/**
 * Class Headers
 * @package storage
 */
class Headers
{
    const MULTIPART_MIXED = 'multipart/mixed';
    const MULTIPART_ALTERNATIVE = 'multipart/alternative';

    /** @var  string */
    private $_headers;
    /** @var  string */
    private $_to;
    /** @var  string */
    private $_from;
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

    /**
     * @param string $headers
     */
    public function __construct($headers)
    {
        $this->_headers = $headers;
        $this->parserHeaders();
    }

    /**
     * Parser headers
     */
    protected function parserHeaders()
    {
        $headers = $this->asArray();

        $this->_to = self::decodeMimeString(current($headers['to']));
        $this->_from = self::decodeMimeString(current($headers['from']));
        $this->_subject = self::decodeMimeString(current($headers['subject']));

        $part = current($headers['content-type']);
        $this->_messageContentType = trim(explode(';', $part)[0]);

        if (preg_match_all('/(boundary|charset)\s*\=\s*["\']?([\w\-\/]+)/si', $part, $result))
        {
            foreach($result[1] as $key=>$val) {
                $val = '_'.$val;
                $this->{$val} = $result[2][$key];
            }
        }
        if (isset($headers['content-transfer-encoding'])) {
            $this->_transferEncoding = trim(current($headers['content-transfer-encoding']));
        }
    }

    /**
     * @param string $strMime
     * @return string
     */
    public static function decodeMimeString($strMime)
    {
        $items = preg_split('/[\r\n]{2,}/si', $strMime);
        $result = '';
        foreach($items as $item) {
            $data = explode('?', $item);
            $str = '';
            if (!empty($data)) {
                array_shift($data);
                $encode = array_shift($data);
                if (array_shift($data) == 'B') {
                    $str = base64_decode(array_shift($data));
                    $str = mb_convert_encoding($str, 'UTF-8', $encode);
                    $str = $str . ltrim($data[0], '=');
                }
            }
            $result .= $str;
        }

        return $result ? $result : $strMime;
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
     * @return array
     */
    public function asArray()
    {
        return self::toArray($this->_headers);
    }

    /**
     * @param string $headers
     * @return array
     */
    public static function toArray($headers)
    {
        preg_match_all('#[\r\n]*([\w-]+\:)(.+?)(?=([\r\n]+[\w-]+\:|[\r\n]{3,}|\n{2,}))#si',$headers,$result);

        $headers = [];
        foreach ($result[1] as $k => $header) {
            $header = strtolower(rtrim($header , ':'));
            $headers[$header][] = trim($result[2][$k]);
        }

        return $headers;
    }
}