<?php
session_start();
header("Content-Type: text/html; charest=utf-8");

class DB
{
    static $db = null;
    static function getDB()
    {
        if (!self::$db) {
            $connection = new PDO("mysql:host=localhost;dbname=obabachu;charset=utf8mb4", "root", "");
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            self::$db = $connection;
        }
        return self::$db;
    }
    static function exec($query)
    {
        try {
            self::getDB()->exec($query);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    static function fetch($query)
    {
        $stmt = self::getDB()->query($query);
        return $stmt->fetch();
    }
    static function fetchAll($query)
    {
        $stmt = self::getDB()->query($query);
        return $stmt->fetchAll();
    }
}