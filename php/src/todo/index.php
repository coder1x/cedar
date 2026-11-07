<?php

class Main
{
  private $host = '';
  private $dbname = '';
  private $username = '';
  private $password = '';

  function __construct()
  {
    $this->host = 'db';
    $this->dbname = 'MYSQL_DATABASE';
    $this->username = 'MYSQL_USER';
    $this->password = 'MYSQL_PASSWORD';

    $this->init();
  }

  private function init()
  {
    Main::credentials();
    $this->connect();
  }

  private static function credentials()
  {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
  }

  private function connect()
  {
    try {
      $this->PDO = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
    } catch (PDOException $pe) {
      die("Could not connect to the database $this->dbname :" . $pe->getMessage());
    }
  }
}
