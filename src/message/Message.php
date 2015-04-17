<?php

namespace afinogen89\getmail\message;

/**
 * Class Message
 * @package storage
 */
class Message
{
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
    public function __construct($header, $message,$id = null)
    {
        $this->id = $id;
        $this->_header = new Headers($header);
        $this->_content = mb_substr($message, strlen($header), strlen($message));
        $this->_originMessage = $message;
        $this->parserContent($this->_header->getMessageBoundary(), $this->_content);
    }

    /**
     * @param string $path
     * @return int
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
    public function getHeader()
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
    public function getAttachment()
    {
        return $this->_attachments;
    }

    /**
     * Разбор тела сообщения
     * @param string $boundary
     * @param string $content
     */
    protected function parserContent($boundary, $content)
    {
        if ($boundary) {
            $parts = preg_split('#--' . $boundary . '(--)?\s*#si', $content, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $part = trim($part);
                if (empty($part)) {
                    continue;
                }

                if (preg_match('/(Content-Type:)(.*)/i', $part, $math)) {
                    if (preg_match('/boundary\s*\=\s*["\']?([\w\-\/]+)/i', str_replace("\r\n\t", ' ', $part), $subBoundary))
                    {
                        if ($subBoundary[1] != $boundary) {
                            $this->parserContent($subBoundary[1], $part);
                        } else {
                            continue;
                        }
                    } else {
                        $data = explode(';', $math[2]);
                        $type = trim($data[0]);

                        //get body message
                        if ($type == Content::CT_MULTIPART_ALTERNATIVE || $type == Content::CT_TEXT_HTML || $type == Content::CT_TEXT_PLAIN || $type == Content::CT_MESSAGE_DELIVERY) {
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
        preg_match('/boundary\s*\=\s*["\']?([\w\-\/]+)/si', $part, $boundary);

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
            $content->charset = trim(explode('=',$data[1])[1]);
            if (isset($headers['content-transfer-encoding'])) {
                $content->transferEncoding = trim(current($headers['content-transfer-encoding']));
            }
        }

        $this->_parts[] = $content;

        if ($content->contentType == Headers::MULTIPART_ALTERNATIVE) {
            $subParts = preg_split('#--' . $content->boundary . '(--)?\s*#si', $part, -1, PREG_SPLIT_NO_EMPTY);
            array_shift($subParts);
            foreach($subParts as $item) {
                $item = self::splitContent(trim($item));
                $subContent = new Content();
                $subContent->boundary = $content->boundary;

                $headers = Headers::toArray($item['header']."\r\n\r\n");
                $data = explode(';', current($headers['content-type']));

                $subContent->contentType = trim($data[0]);
                $subContent->charset = trim(explode('=',$data[1])[1]);

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

        if (isset($headers['content-type'])) {
            $data = explode(';', current($headers['content-type']));

            $attachment->contentType = trim($data[0]);

            //если нет имени - текущее время
            $name = isset($data[1]) ? trim(substr($data[1], 6), '"') : time();
            $tmp = Headers::decodeMimeString($name);

            if (!empty($tmp)) {
                $attachment->name = $tmp;
            } else {
                $attachment->name = $name;
            }
        }

        if (isset($headers['content-disposition'])) {
            $data = explode(';', current($headers['content-disposition']));

            $attachment->contentDisposition = trim($data[0]);
            if (isset($data[1])) {
                $name = trim(substr($data[1], 10), '"');
                $tmp = Headers::decodeMimeString($name);
                if (!empty($tmp)) {
                    $attachment->filename = $tmp;
                } else {
                    $attachment->filename = $name;
                }
            } else {
                $attachment->filename = $attachment->name;
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
     * @param string $str
     * @return array
     */
    public static function splitContent($str)
    {
        $data = preg_split('/[\r\n]{3,}/si', $str);

        return ['header' => array_shift($data), 'content' => implode("\r\n", $data)];
    }
}