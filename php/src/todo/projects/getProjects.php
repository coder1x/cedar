<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function getData($main, $token)
{
  try {
    $sql = 'SELECT * FROM projects WHERE token = :token';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];

  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = [
      'id' => (int) $item['project_id'],
      'text' => $item['name'],
      'position' => (int) $item['position']
    ];
  }

  return outputData($data);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $sessionToken = htmlspecialchars($_GET['token']) ?? '';

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $token = getInternalToken($main, $sessionToken)['token'];

  if (!$token) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  echo json_encode(getData($main, $token));

  return true;
}

model($main);