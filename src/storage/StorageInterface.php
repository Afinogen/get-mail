<?php
/**
 * Created by PhpStorm.
 * User: afinogen
 * Date: 08.09.16
 * Time: 14:05
 */

namespace afinogen89\getmail\storage;

use afinogen89\getmail\message\Message;

/**
 * Interface StorageInterface
 *
 * @package afinogen89\getmail\storage
 */
interface StorageInterface
{
    /**
     * Получение количества сообщений
     *
     * @return int
     */
    public function countMessages();

    /**
     * Закрытие протокола
     */
    public function close();

    /**
     * Получение сообщения
     *
     * @param int $id
     *
     * @return Message
     */
    public function getMessage($id);

    /**
     * Удаление сообщения
     *
     * @param int $id
     */
    public function removeMessage($id);
}