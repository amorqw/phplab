<?php
$dsn = "pgsql:host=localhost;dbname=quiz;user=admin;password= 123";
try{
    $pdo = new PDO($dsn);
    print_r("ПОДКЛЮЧИЛИСЬ");
}
catch(\PDOException $e){
    print_r("ОШИБКА!!!!!!!: " . $e->getMessage());
}
?>


