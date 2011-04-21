<?php
    header( 'Content-type: text/plain' );
    try
    {
        $db = new PDO('sqlite:openra.db');
        echo 'Connection to DB established.\n';
        if ($db->query('DROP TABLE servers'))
            echo 'Dropped table.\n';
        $schema = 'CREATE TABLE servers (id INTEGER PRIMARY KEY AUTOINCREMENT,  
            address varchar(255) UNIQUE, yaml text, portOpen varchar(3), ts integer)';
        if ($db->query($schema))
            echo 'Created table.';
        $db = null;
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
    }
?>