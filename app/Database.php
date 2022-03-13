<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if(self::$connection === null) {
            try {
                self::$connection = new PDO('mysql:host=localhost;dbname=booking', 'phpadmin', 'A.Kronvalda.56');
            } catch (PDOException $e) {
                print "Error!" . $e->getMessage() . "<br/>";
                die();
            }
        }
        return self::$connection;
    }
}