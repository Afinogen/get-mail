<?php

namespace afinogen89\getmail\message;

/**
 * Class Attachment
 *
 * @package storage
 */
class Attachment
{
    /**
     * @var string
     */
    public $contentType;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $contentDisposition;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $attachmentId;

    /**
     * @var string
     */
    public $transferEncoding;

    /**
     * @var string
     */
    public $data;

    /** @var  string */
    public $headers;

    /**
     * @return string|null
     */
    public function getData()
    {
        if ($this->transferEncoding == 'base64') {
            return base64_decode($this->data);
        }elseif ($this->transferEncoding == 'quoted-printable') {
            return quoted_printable_decode($this->data);
        } else {
            //TODO возвращать исходный контент?
//          return $this->data;
        }

        return null;
    }

    /**
     * @param string $path
     */
    public function saveToFile($path)
    {
        file_put_contents($path, $this->getData());
    }
}