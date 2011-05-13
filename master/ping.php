<?php
    define('PORT_CHECK_TIMEOUT', 3);
    
    function check_port($ip, $port)
    {
        $sock = @fsockopen($ip, $port, $errno, $errstr, PORT_CHECK_TIMEOUT);
        if (!$sock)
            return false;
        fclose($sock);
        return true;
    }
    
    if (!isset( $_REQUEST['port'] )) exit();
    
    
    if ( isset( $_REQUEST['yaml'] ) ) {
        $yaml = $_REQUEST['yaml'];
		$yaml = str_replace("\n", "\n\t", $yaml);
		if($yaml[0] != "\t")
			$yaml = "\t" . $yaml;
    } else {
        // Deal with legacy
        if (!isset( $_REQUEST['name'] )) exit();
        if (!isset( $_REQUEST['state'] )) exit();
        if (!isset( $_REQUEST['map'] )) exit();
        if (!isset( $_REQUEST['mods'] )) exit();
        if (!isset( $_REQUEST['players'] )) exit();
        $yaml = "\tName: " . $_REQUEST['name'] . "\n"
                ."\tState: " . $_REQUEST['state'] . "\n"
                ."\tPlayers: " . $_REQUEST['players'] . "\n"
                ."\tMap: " . $_REQUEST['map'] . "\n"
                ."\tMods: " . $_REQUEST['mods'] . "\n";
    }
    
    header( 'Content-type: text/plain' );
    try 
    {
        $db = new PDO('sqlite:openra.db');
        $ip = $_SERVER['REMOTE_ADDR'];
        $port = $_REQUEST['port'];
        $addr = $ip . ':' . $port;
        $name = urldecode( $_REQUEST['name'] );
        
        if (isset( $_REQUEST['new']))
        {
            $connectable = check_port($ip, $port);
            $portOpen = 'yes';
            if (!$connectable)
                $portOpen = 'no';
        } else { 
			$select = $db->prepare('SELECT portOpen FROM servers WHERE address = :addr');
			$select->bindValue(':addr', $addr, PDO::PARAM_STR);
			$select->execute();
			$portOpen = $select->fetchColumn();
		}
		$insert = $db->prepare('INSERT OR REPLACE INTO servers 
							(address, portOpen, yaml, ts) 
							VALUES (:addr, :portOpen, :yaml, :time)');
        $insert->bindValue(':addr', $addr, PDO::PARAM_STR);
        $insert->bindValue(':yaml', $yaml, PDO::PARAM_STR);
        $insert->bindValue(':time', time(), PDO::PARAM_INT);
		$insert->bindValue(':portOpen', $portOpen, PDO::PARAM_STR);
        
        $insert->execute();

        if (isset( $_REQUEST['new']))
        {
            $select = $db->prepare('SELECT id FROM servers WHERE address = :addr');
            $select->bindValue(':addr', $addr, PDO::PARAM_STR);

            $select->execute();

            echo (int)$select->fetchColumn();
    
            $games = file_get_contents("../games.txt");
            file_put_contents("../games.txt", $games + 1);
        }

        $db = null;
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
    }
?>