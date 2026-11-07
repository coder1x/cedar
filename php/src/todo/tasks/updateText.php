<?php
require_once '../index.php';
require_once '../getInternalToken.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function updateText($main, $token, $text, $id)
{

  try {
    $sql = 'UPDATE tasks SET text = :text WHERE task_id = :task_id AND token = :token';
    $statement  =  $main->PDO->prepare($sql);
    $statement->bindValue(':text', $text);
    $statement->bindValue(':task_id', $id);
    $statement->bindValue(':token', $token);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }


  return outputData(true);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));

  $sessionToken = htmlspecialchars($param->token) ?? '';
  $text = $param->text;
  $id = $param->id;

  if (!$id) {
    echo json_encode(messageError('Не выбрана задача для редактирования', 20));

    return false;
  }

  if (!$text) {
    echo json_encode(messageError('Отсутствует текст задачи', 20));

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

  echo json_encode(updateText($main, $token, $text, $id));

  return true;
}

model($main);
