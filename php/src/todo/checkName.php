<?php
require_once 'index.php';
require_once 'messageError.php';
require_once 'outputData.php';

$main = new Main();

function checkName($main, $nameUser)
{
  try {
    $sql = 'SELECT * FROM users WHERE name = :name LIMIT 1';
    $statement  =  $main->PDO->prepare($sql);
    $statement->bindValue(':name', $nameUser);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = $item['name'];
  }

  if (count($data) > 0) {
    return outputData($data[0] == $nameUser);
  }

  return outputData(false);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $nameUser = htmlspecialchars($_GET['name']) ?? '';

  if (!$nameUser) {
    echo json_encode(messageError('Имя пользователя отсутствует', 20));

    return false;
  }

  echo json_encode(checkName($main, $nameUser));

  return true;
}

model($main);
