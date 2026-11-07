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
    $sql = 'SELECT * FROM tasks WHERE token = :token AND task_id = :task_id';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':task_id', $from->id);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = (object) [];

  if ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data = (object) [
      'project_id' => (int) $item['project_id'],
      'status' => (int) $item['status'],
    ];
  }

  $project_id = $data->project_id;
  $status = $data->status;

  try {

    $direction = $from->position > $to->position;

    $range = $direction ? ':to AND :from' : ':from AND :to';
    $shift = $direction ? '+ 1' : '- 1';

    $sql = 'UPDATE tasks SET position = position ' . $shift . ' WHERE position BETWEEN ' . $range . ' AND token = :token AND project_id = :project_id AND status = :status';

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':project_id', $project_id);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':from', $direction ? $from->position - 1 : $from->position + 1);
    $statement->bindValue(':to', $direction ? $to->position + 1 : $to->position - 1);
    $statement->execute();

    $sql = 'UPDATE tasks SET position = :to_position WHERE task_id = :from_id AND token = :token AND project_id = :project_id AND status = :status'; // From

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':to_position', $to->position);
    $statement->bindValue(':from_id', $from->id);
    $statement->bindValue(':project_id', $project_id);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':token', $token);
    $statement->execute();

    $sql = 'UPDATE tasks SET position = :to_position WHERE task_id = :to_id AND token = :token AND project_id = :project_id AND status = :status'; // To

    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':to_position', $direction ? $to->position + 1 : $to->position - 1);
    $statement->bindValue(':to_id', $to->id);
    $statement->bindValue(':project_id', $project_id);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':token', $token);
    $statement->execute();

    $sql = 'SELECT * FROM tasks WHERE token = :token AND project_id = :project_id';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':project_id', $project_id);
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
    $statement->bindValue(':project_id', $project_id);
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