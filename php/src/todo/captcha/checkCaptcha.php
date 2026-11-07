<?php
require_once '../index.php';
require_once '../messageError.php';
require_once '../outputData.php';

$main = new Main();

function checkCaptcha($main, $code, $token)
{
  $code = md5(strtoupper($code));

  try {
    $sql = 'SELECT * FROM captcha WHERE token = :token AND code = :code LIMIT 1';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':code', $code);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = $item['code'];
  }

  if (count($data) > 0) {
    return outputData($data[0] == $code);
  }

  return outputData(false);
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $code = htmlspecialchars($_GET['code']) ?? '';
  $token = htmlspecialchars($_GET['token']) ?? '';

  if (!$code) {
    echo json_encode(messageError('Не указан код для проверки', 20));

    return false;
  }

  if (!$token) {
    echo json_encode(messageError('Не указан токен', 20));

    return false;
  }

  echo json_encode(checkCaptcha($main, $code, $token));

  return true;
}

model($main);
