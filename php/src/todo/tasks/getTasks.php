<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function getData($main, $token, $projectId)
{
  try {
    $sql = 'SELECT * FROM tasks WHERE token = :token AND project_id = :project_id';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':project_id', $projectId);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];

  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = [
      'id' => (int) $item['task_id'],
      'text' => $item['text'],
      'status' => (int) $item['status'],
      'position' => (int) $item['position']
    ];
  }

  try {
    $sql = 'SELECT * FROM projects WHERE token = :token AND project_id = :project_id';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':project_id', $projectId);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $projectName = '';

  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $projectName = $item['name'];
  }

  return outputData(
    [
      'title' => $projectName,
      'data' => $data,
    ]
  );
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $sessionToken = htmlspecialchars($_GET['token']) ?? '';
  $projectId = htmlspecialchars($_GET['projectId']) ?? '';

  if (!$sessionToken) {
    echo json_encode(messageError('Некорректный session-token', 30));

    return false;
  }

  $token = getInternalToken($main, $sessionToken)['token'];

  if (!$token) {
    echo json_encode(messageError('Не удалось получить token', 40));

    return false;
  }

  echo json_encode(getData($main, $token, $projectId));

  return true;
}

model($main);