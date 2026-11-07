<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function remove($main, $token, $tasksId)
{

  try {
    foreach ($tasksId as &$id) {
      $sql = 'DELETE FROM tasks WHERE token = :token AND task_id = :task_id';
      $statement = $main->PDO->prepare($sql);
      $statement->bindValue(':token', $token);
      $statement->bindValue(':task_id', $id);
      $statement->execute();
    }
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  return outputData(true);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));
  $sessionToken = htmlspecialchars($param->token) ?? '';
  $tasksId = $param->tasksId;

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $token = getInternalToken($main, $sessionToken)['token'];

  if (!$token) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  if (!$tasksId) {
    echo json_encode(messageError('Не указан id удаляемого элемента', 20));

    return false;
  }

  echo json_encode(remove($main, $token, $tasksId));

  return true;
}

model($main);
