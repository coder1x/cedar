<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function remove($main, $token, $projectsID)
{

  try {
    foreach ($projectsID as &$id) {
      $sql = 'DELETE FROM projects WHERE token = :token AND project_id = :project_id';
      $statement = $main->PDO->prepare($sql);
      $statement->bindValue(':token', $token);
      $statement->bindValue(':project_id', $id);
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
  $projectsID = $param->projectsId;

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $token = getInternalToken($main, $sessionToken)['token'];

  if (!$token) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  if (!$projectsID) {
    echo json_encode(messageError('Не выбраны элементы для удаления', 20));

    return false;
  }

  echo json_encode(remove($main, $token, $projectsID));

  return true;
}

model($main);
