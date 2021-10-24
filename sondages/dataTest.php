<?php


//$this->connection = new PDO("sqlite:database.sqlite");

$encrypt = password_hash('epfc', PASSWORD_BCRYPT);
var_dump($encrypt);