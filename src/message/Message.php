<?php

namespace afinogen89\getmail\message;

use ForceUTF8\Encoding;

/**
 * Class Message
 *
 * @package storage
 */
class Message
{
    const FILE_PATTERN = '#name\s*(\*\d+\*)?\s*\=(utf-8|koi8-r)?\s*[\\\'\"]*([^\\\'";]+)#si';
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    private $_originMessage;

    /**
     * @var Headers
     */
    private $_header;

    /**
     * @var string
     */
    private $_content;

    /**
     * @var Content[]
     */
    private $_parts = [];

    /**
     * @var Attachment[]
     */
    private $_attachments = [];

    /**
     * @param string $message
     * @param null|int $id
     */
    public function __construct($header, $message, $id = null)
    {
        $this->id = $id;
        $this->_header = new Headers($header);
        $this->_content = mb_substr($message, mb_strlen($header), mb_strlen($message));
        $this->_originMessage = $message;
        $this->parserContent($this->_header->getMessageBoundary(), $this->_content);
    }

    /**
     * @param string $path
     * @return false|int
     */
    public function saveToFile($path)
    {
        return file_put_contents($path, $this->_originMessage);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->_header;
    }

    /**
     * @return Content[]
     */
    public function getParts()
    {
        return $this->_parts;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * Текст письма
     *
     * @return string|null
     */
    public function getMsgBody()
    {
        $body = null;

        if (!empty($this->_parts)) {
            if (count($this->_parts) > 1) {
                foreach ($this->_parts as $part) {
                    if ($part->contentType != Content::CT_TEXT_PLAIN && $this->getHeaders()->getMessageContentType() == Content::CT_MULTIPART_ALTERNATIVE) {
                        $body = $part->getContentDecode();
                        break;
                    }

                    $body .= PHP_EOL.$part->getContentDecode();
                }
            } else {
                $body = $this->_parts[0]->getContentDecode();
            }
        }

        return $body;
    }

    /**
     * Альтернативный текст письма
     *
     * @return string|null
     */
    public function getMsgAlternativeBody()
    {
        if (
            !empty($this->_parts)
            && (
                $this->getHeaders()->getMessageContentType() == Content::CT_MULTIPART_ALTERNATIVE
                || $this->getHeaders()->getMessageContentType() == Content::CT_MULTIPART_MIXED
            )
        ) {
            foreach ($this->_parts as $part) {
                if ($part->contentType == Content::CT_TEXT_PLAIN) {
                    return $part->getContentDecode();
                }
            }
        }

        return null;
    }

    /**
     * Разбор тела сообщения
     *
     * @param string $boundary
     * @param string $content
     */
    protected function parserContent($boundary, $content)
    {
        if ($boundary) {
            $parts = preg_split('#--'.$boundary.'(--)?\s*#si', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }

                if (preg_match('/(Content-Type:)(.*)/i', $part, $math)) {
                    if (preg_match(Headers::BOUNDARY_PATTERN, str_replace("\r\n\t", ' ', $part), $subBoundary)) {
                        if ($subBoundary[1] != $boundary) {
                            $this->parserContent($subBoundary[1], $part);
                        } else {
                            continue;
                        }
                    } else {
                        $data = explode(';', $math[2]);
                        $type = trim($data[0]);

                        $isAttachment = (strpos($part, 'Content-Disposition: attachment;') !== false);

                        //get body message
                        if (($type == Content::CT_MULTIPART_ALTERNATIVE || $type == Content::CT_TEXT_HTML || $type == Content::CT_TEXT_PLAIN
                                || $type == Content::CT_MESSAGE_DELIVERY)
                            && !$isAttachment
                        ) {
                            $this->parserBodyMessage($part);
                        } elseif ($type == Content::CT_MESSAGE_RFC822) {
                            $this->parserBodyMessage($part);
                            $this->parserAttachment($part);
                        } else { //attachment
                            $this->parserAttachment($part);
                        }
                    }
                }
            }
        } else {
            $content = new Content();
            $content->content = $this->_content;
            $content->charset = $this->_header->getCharset();
            $content->contentType = $this->getHeaders()->getMessageContentType();
            $content->transferEncoding = $this->_header->getTransferEncoding();

            $this->_parts[] = $content;
        }
    }

    /**
     * @param string $part
     */
    protected function parserBodyMessage($part)
    {
        preg_match('/Content-Type\:\s*([\w\-\/]+)/si', $part, $contentType);
        preg_match(Headers::BOUNDARY_PATTERN, $part, $boundary);

        $contentType = $contentType[1];
        if (isset($boundary[1])) {
            $boundary = trim($boundary[1]);
        }

        $dataContent = self::splitContent($part);

        $content = new Content();
        $content->contentType = $contentType;
        $content->boundary = $boundary ?: $this->_header->getMessageBoundary();
        $content->content = $dataContent['content'];

        if ($content->contentType == Content::CT_TEXT_HTML || $content->contentType == Content::CT_TEXT_PLAIN) {
            $headers = Headers::toArray($dataContent['header']."\r\n\r\n");
            $data = explode(';', current($headers['content-type']));
            if (count($data) > 1 && strpos($data[1], 'charset') !== false) {
                $content->charset = trim(explode('=', $data[1])[1]);
            } else {
                $content->charset = $this->getHeaders()->getCharset();
            }

            if (isset($headers['content-transfer-encoding'])) {
                $content->transferEncoding = trim(current($headers['content-transfer-encoding']));
            }
        }

        $this->_parts[] = $content;

        if ($content->contentType == Headers::MULTIPART_ALTERNATIVE) {
            $subParts = preg_split('#--'.$content->boundary.'(--)?\s*#si', $part, -1, PREG_SPLIT_NO_EMPTY);
            array_shift($subParts);
            foreach ($subParts as $item) {
                $item = self::splitContent(trim($item));
                $subContent = new Content();
                $subContent->boundary = $content->boundary;

                $headers = Headers::toArray($item['header']."\r\n\r\n");
                $data = explode(';', current($headers['content-type']));

                $subContent->contentType = trim($data[0]);
                $subContent->charset = trim(explode('=', $data[1])[1]);

                $subContent->transferEncoding = trim(current($headers['content-transfer-encoding']));

                $subContent->content = $item['content'];

                $this->_parts[] = $subContent;
            }
        }
    }

    /**
     * @param string $part
     */
    protected function parserAttachment($part)
    {
        $part = self::splitContent($part);
        $attachment = new Attachment();
        $headers = Headers::toArray($part['header']."\r\n");
        $attachment->headers = $headers;

        $pattern = self::FILE_PATTERN;
        $name = '';
        $isNotFoundName = false;
        if (isset($headers['content-type'])) {
            $data = explode(';', current($headers['content-type']));

            $attachment->contentType = trim($data[0]);

            $name = $this->decodeFileName($data);
            if ($name == null) {
                $isNotFoundName = true;
                $name = time();
            }
            $attachment->name = $name;
        }

        if (isset($headers['content-disposition'])) {
            $data = explode(';', current($headers['content-disposition']));
            $attachment->contentDisposition = trim($data[0]);

            $tmpName = $data;

            unset($tmpName[0]);
            foreach ($tmpName as $key => $val) {
                if (preg_match('/[\w\-]{3,}\=/i', trim($val), $result) && stripos($result[0], 'name') === false) {
                    unset($tmpName[$key]);
                } else {
                    $tmpName[$key] = preg_replace('/(file)?name\s*(\*\d+\*)?\s*\=/i', '', $val);
                }
            }

            $tmpName = implode($tmpName);
            $tmpName = preg_replace('#\s+#s', "\n\n", $tmpName);
            if (preg_match_all($pattern, $tmpName, $result)) {
                $name = [];
                foreach ($result[3] as $v) {
                    $name[] = $v;
                }

                $name = implode('', $name);
                $name = Headers::decodeMimeString($name);
                $name = urldecode($name);

                $attachment->filename = $this->toUtf8($name);
            } else {
                if ($isNotFoundName) {
                    $name = $this->decodeFileName($data);
                    if ($name === null) {
                        $name = time();
                    }
                }
                $name = trim(preg_replace('/(file)?name\s*(\*\d+\*)?\s*\=/i', '', $name));
                $name = urldecode($name);
                $attachment->filename = $name;//Headers::decodeMimeString($name);
            }
        }

        if (isset($headers['content-transfer-encoding'])) {
            $attachment->transferEncoding = trim(current($headers['content-transfer-encoding']));
        }

        if (isset($headers['x-attachment-id'])) {
            $attachment->attachmentId = trim(current($headers['x-attachment-id']));
        }

        $attachment->data = trim($part['content']);

        $this->_attachments[] = $attachment;
    }

    /**
     * Попытка обнаружить utf-8
     *
     * @param string $string
     *
     * @return false|int
     */
    public function detectUTF8($string)
    {
        return preg_match(
            '%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs',
            $string
        );
    }

    /**
     * Конвертация в utf-8
     *
     * @param $string
     *
     * @return false|string|string[]|null
     */
    public function toUtf8($string)
    {
        $encode = mb_detect_encoding(
            $string, [
                'UTF-8',
                'Windows-1251'
            ]
        );
        if ($encode && $encode !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encode);
        }

        if (!$this->detectUTF8($string)) {
            //Скорей всего кодировка не utf-8, пробуем перегнать из 1251
            $tmpName = mb_convert_encoding($string, 'UTF-8', 'Windows-1251');
            if ($this->detectUTF8($tmpName)) {
                $string = $tmpName;
            }
        }

        return $string;
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public static function splitContent($str)
    {
        $data = preg_split('/[\r\n]{3,}/si', $str);

        return [
            'header' => array_shift($data),
            'content' => implode("\r\n", $data)
        ];
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function decodeFileName($data)
    {
        array_shift($data);
        $name = '';
        if (count($data) == 1) {
            $name = preg_replace('#.*name\s*\=\s*[\'"]([^\'"]+).*#si', '$1', $data[0]);
        } elseif (count($data) > 1) {
            foreach ($data as $value) {
                if (preg_match(self::FILE_PATTERN, $value, $res)) {
                    $name .= $res[3];
                }
            }
        } else {
            return null;
        }

        $tmpFileName = Headers::decodeMimeString($name);
        //TODO не очень хорошее решение, если кто предложит лучше, готов рассмотреть
        // Content-Disposition: attachment; filename*=UTF-8''%D0%B7%D0%B0%D1%8F%D0%B2%D0%BA%D0%B0%20%D0%95%D0%B2%D1%80%D0%BE%D0%94%D0%BE%D0%BD%208%D1%84%D0%B5%D0%B2%D1%802017%2Exls
        if ($tmpFileName === $name) {
            $tmp = explode("''", $name);
            if (count($tmp) > 1) {
                $tmpFileName = urldecode($tmp[1]);
            }
        }

        return $this->toUtf8($tmpFileName);
    }
}