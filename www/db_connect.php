<?php
$dbname = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$dsn = "pgsql:host=db;dbname=$dbname;user=$user;password=$pass";
try{
    $pdo = new PDO($dsn);
    print_r("ПОДКЛЮЧИЛИСЬ");
}
catch(\PDOException $e){
    print_r("ОШИБКА!!!!!!!: " . $e->getMessage());
}
?>


