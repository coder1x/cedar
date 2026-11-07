<?php
require_once '../index.php';
require_once '../messageError.php';
require_once '../outputData.php';
require_once '../getInternalToken.php';

$main = new Main();

function add($main, $dataUser, $text)
{
  $id = $dataUser['id'];
  $token = $dataUser['token'];

  try {
    $sql = 'INSERT INTO projects (project_id, users_id, token, name) VALUES (:project_id, :users_id, :token, :name)';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':project_id', NULL);
    $statement->bindValue(':users_id', $id);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':name', $text);
    $statement->execute();

    $sql = 'SELECT MAX(project_id), MAX(position) FROM projects WHERE token = :token';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $ID = 0;
  $position = 0;
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $ID = $item['MAX(project_id)'];
    $position = $item['MAX(position)'];
  }

  return outputData(
    [
      'id' => $ID,
      'position' => $position,
    ]
  );
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));
  $sessionToken = htmlspecialchars($param->token) ?? '';
  $text = $param->text ?? '';

  if (!$text) {
    echo json_encode(messageError('Нет описания проекта', 20));

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

  echo json_encode(add($main, $dataUser, $text));

  return true;
}

model($main);