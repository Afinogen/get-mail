<?php

namespace afinogen89\getmail\protocol;

/**
 * Class File
 * @package afinogen89\getmail\protocol
 */
class File
{
    /** @var  string */
    private $_path;

    /** @var array  */
    private $_mails = [];

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        if (is_dir($path)) {
            $this->_path = rtrim($path, '/');
            $this->readDir();
        }
    }

    /**
     * Деструктор класса и уделение помеченных на удаление писем
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * Закрытие протокола, удаление файлов
     */
    public function logout()
    {
        if ($this->_mails) {
            foreach($this->_mails as $mail) {
                if ($mail['is_deleted']) {
                    unlink($this->_path.'/'.$mail['file_name']);
                }
            }
        }
    }

    /**
     * Считывание писем из папки
     */
    public function readDir()
    {
        $files = scandir($this->_path);
        foreach($files as $file) {
            $path_info = pathinfo($file);
            if ($path_info['extension'] == 'eml') {
                $this->_mails[] = ['is_deleted' => 0, 'file_name' => $file];
            }
        }
    }

    /**
     * Количество сообщений
     * @return int
     */
    public function countMessage()
    {
        return count($this->_mails);
    }

    /**
     * Получение размера писем
     * @param null|int $id
     * @return array|null
     */
    public function getList($id = null)
    {
        if ($this->_mails) {
            if ($id != null && isset($this->_mails[$id])) {
                return [filesize($this->_path.'/'.$this->_mails[$id]['file_name'])];
            }

            $result = [];
            foreach($this->_mails as $mail) {
                $result[] = filesize($this->_path.'/'.$mail['file_name']);
            }
            return $result;
        }
        return null;
    }

    /**
     * Удаление письма по номеру в списке
     * @param int $id
     */
    public function delete($id)
    {
        if ($this->_mails && isset($this->_mails[$id])) {
            $this->_mails[$id]['is_deleted'] = true;
        }
    }

    /**
     * Отмена удаления письмо по id или всех писем в списке
     * @param null|int $id
     */
    public function undelete($id = null)
    {
        if ($this->_mails) {
            if ($id != null && isset($this->_mails[$id])) {
                $this->_mails[$id]['is_deleted'] = false;
            } else {
                foreach($this->_mails as $mail) {
                    $mail['is_deleted'] = false;
                }
            }
        }
    }

    /**
     * @param $id
     * @return null|string
     */
    public function top($id)
    {
        if ($this->_mails && isset($this->_mails[$id])) {
            $data = file_get_contents($this->_path.'/'.$this->_mails[$id]['file_name']);
            $data = explode("\r\n\r\n", $data);
            return $data[0];
        } else {
            return null;
        }
    }

    /**
     * @param $id
     * @return null|string
     */
    public function retrieve($id)
    {
        if ($this->_mails && isset($this->_mails[$id])) {
            return file_get_contents($this->_path.'/'.$this->_mails[$id]['file_name']);
        } else {
            return null;
        }
    }
}