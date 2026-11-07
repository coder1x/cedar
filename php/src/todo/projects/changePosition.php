<?php
require_once '../index.php';
require_once '../messageError.php';
require_once '../outputData.php';
require_once '../getInternalToken.php';

$main = new Main();

function changePosition($main, $dataUser, $from, $to)
{
  $token = $dataUser['token'];

  try {
    $direction = $from->position > $to->position;

    $range = $direction ? ':to AND :from' : ':from AND :to';
    $shift = $direction ? '+ 1' : '- 1';

    $sql = 'UPDATE projects SET position = position ' . $shift . ' WHERE position BETWEEN ' . $range . ' AND token = :token';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':from', $direction ? $from->position - 1 : $from->position + 1);
    $statement->bindValue(':to', $direction ? $to->position + 1 : $to->position - 1);
    $statement->execute();

    $sql = 'UPDATE projects SET position = :to_position WHERE project_id = :from_id AND token = :token'; // From

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':to_position', $to->position);
    $statement->bindValue(':from_id', $from->id);
    $statement->bindValue(':token', $token);
    $statement->execute();

    $sql = 'UPDATE projects SET position = :to_position WHERE project_id = :to_id AND token = :token'; // To

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':to_position', $direction ? $to->position + 1 : $to->position - 1);
    $statement->bindValue(':to_id', $to->id);
    $statement->bindValue(':token', $token);
    $statement->execute();

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
  if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));
  $sessionToken = htmlspecialchars($param->token) ?? '';
  $from = $param->from;
  $to = $param->to;

  if (!$from || !$to) {
    echo json_encode(messageError('Нет данных о позиции перемещаемых элементов', 20));

    return false;
  }

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $dataUser = getInternalToken($main, $sessionToken);

  if (!$dataUser['token']) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  echo json_encode(changePosition($main, $dataUser, $from, $to));

  return true;
}

model($main);