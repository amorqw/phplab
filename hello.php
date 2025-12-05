<?php
$dbconn = pg_connect("host=localhost dbname=phplab user=www password=123")
or die('Не удалось соединиться: ' . pg_last_error());