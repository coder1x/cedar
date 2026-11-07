<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function updateStatus($main, $token, $status, $tasksId)
{

  try {
    foreach ($tasksId as &$id) {
      $sql = 'UPDATE tasks SET status = :status WHERE task_id = :task_id AND token = :token';
      $statement = $main->PDO->prepare($sql);
      $statement->bindValue(':status', $status);
      $statement->bindValue(':task_id', $id);
      $statement->bindValue(':token', $token);
      $statement->execute();
    }
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  return outputData(true);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));

  $sessionToken = htmlspecialchars($param->token) ?? '';
  $status = $param->status;
  $tasksId = $param->tasksId;

  if (!$tasksId) {
    echo json_encode(messageError('Не выбрана задача для переноса', 20));

    return false;
  }

  if (!$status) {
    echo json_encode(messageError('Не указано куда переносить задачу', 20));

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

  echo json_encode(updateStatus($main, $token, $status, $tasksId));

  return true;
}

model($main);
