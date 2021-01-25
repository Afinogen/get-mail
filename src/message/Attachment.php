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
    public $baseName;

    /**
     * @var string|null
     */
    public $extension = null;

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
        } elseif ($this->transferEncoding == 'quoted-printable') {
            return quoted_printable_decode($this->data);
        } elseif (in_array($this->transferEncoding, ['8bit', '7bit', 'binary'])) {
            return $this->data;
        } else {
            //TODO возвращать исходный контент?
//          return $this->data;
        }

        return null;
    }

    /**
     * @param string $path
     * @return false|int
     */
    public function saveToFile($path)
    {
        return file_put_contents($path, $this->getData());
    }
}
