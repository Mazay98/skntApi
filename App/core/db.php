<?php
require_once 'db_cfg.php';
class Db
{
    public function getConnect()
    {
        try {
            return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME , DB_USER, DB_PASSWORD);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}