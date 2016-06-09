<?php

namespace afinogen89\getmail\message;

/**
 * Class Content
 *
 * @package storage
 */
class Content
{
    const CT_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CT_TEXT_PLAIN = 'text/plain';
    const CT_TEXT_HTML = 'text/html';
    const CT_MESSAGE_DELIVERY = 'message/delivery-status';
    const CT_MESSAGE_RFC822 = 'message/rfc822';

    /**
     * @var string
     */
    public $contentType;

    /**
     * @var string
     */
    public $boundary;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $charset;

    /**
     * @var string
     */
    public $transferEncoding;

    /**
     * @return string
     */
    public function getContentDecode()
    {
        $content = '';
        if ($this->transferEncoding == 'base64') {
            $content = base64_decode($this->content);
        } elseif ($this->transferEncoding == 'quoted-printable') {
            $content = quoted_printable_decode($this->content);
        } else {
            $content = $this->content;
        }

        if ($this->charset && strtolower($this->charset) != 'utf-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $this->charset);
        }

        return $content;
    }
}