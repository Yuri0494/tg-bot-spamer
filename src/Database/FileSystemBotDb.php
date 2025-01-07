<?php

namespace App\Database;

use Exception;

class FileSystemBotDb {
    private string $path;
    private array $db;

    public function __construct($path)
    {
        $this->path = $path;
        $this->db = $this->connectToDb($path);
    }

    public function getAllChats()
    {
        return $this->db['chats'];
    }

    public function getChatIdInfo($chatId)
    {
        return $this->db['chats'][$chatId];
    }

    public function getLastCommandByChatId($chatId): string
    {

        if(!$this->chatIdExists($chatId)) {
            throw new Exception('Такого пользователя не существует');
        };
        
        return $this->db['chats'][$chatId]['lastCommand'];
    }

    public function setLastCommandByChatId($chatId, string $command): void
    {   
        if(!$this->chatIdExists($chatId)) {
            throw new Exception('Такого пользователя не существует');
        };

        $this->db['chats'][$chatId] = [
            'lastCommand' => $command
        ];

        $this->save();
    }

    public function setChatId($chatId)
    {
        if($this->chatIdExists($chatId)) {
            return;
        }

        $this->db['chats'][$chatId] = [
            'lastCommand' => ''
        ];

        $this->save();
    }

    public function unsetChatId($chatId)
    {
        if(!$this->chatIdExists($chatId)) {
            return;
        }

        unset($this->db['chats'][$chatId]);

        $this->save();
    }

    public function chatIdExists($chatId) 
    {
        return array_key_exists($chatId, $this->getAllChats());
    }

    private function connectToDb(string $dbPath) 
    {
        $db = file_get_contents($dbPath);

        if(!$db) {
            file_put_contents($dbPath, json_encode([
                'chats' => []
            ]));
        }

       return json_decode(file_get_contents($dbPath), true);
    }

    private function save() 
    {
        file_put_contents($this->path, json_encode($this->db));
    }
}