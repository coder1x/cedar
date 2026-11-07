<?php
require_once '../index.php';
require_once '../messageError.php';
require_once '../outputData.php';
require_once '../getInternalToken.php';

$main = new Main();

function add($main, $token, $text, $projectId)
{
  try {
    $sql = 'INSERT INTO tasks (task_id, project_id, token, text, status) VALUES (:task_id, :project_id, :token, :text, :status)';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':task_id', NULL);
    $statement->bindValue(':project_id', $projectId);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':text', $text);
    $statement->bindValue(':status', 1);
    $statement->execute();

    $sql = 'SELECT MAX(task_id), MAX(position) FROM tasks WHERE token = :token';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $ID = 0;
  $position = 0;
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $ID = $item['MAX(task_id)'];
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
  $text = $param->text;
  $projectId = htmlspecialchars($param->projectId);

  if (!$text) {
    echo json_encode(messageError('Нет описания задачи', 20));

    return false;
  }

  if (!$projectId) {
    echo json_encode(messageError('Не указан id проекта', 20));

    return false;
  }

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $token = getInternalToken($main, $sessionToken)['token'];

  if (!$token) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  echo json_encode(add($main, $token, $text, $projectId));

  return true;
}

model($main);