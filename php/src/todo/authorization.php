<?php
require_once 'index.php';
require_once 'messageError.php';
require_once 'outputData.php';
require_once 'generateToken.php';

$main = new Main();

function authorization($main, $nameUser, $password)
{
  try {
    $sql = 'SELECT * FROM users WHERE name LIKE :name AND password LIKE :password LIMIT 1';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':name', $nameUser);
    $statement->bindValue(':password', $password);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = $item['token'];
  }

  if (count($data) > 0) {
    $sessionToken = generateToken($password);

    try {
      $sql = 'UPDATE users SET sessionToken= :sessionToken WHERE token= :token';
      $statement = $main->PDO->prepare($sql);
      $statement->bindValue(':sessionToken', $sessionToken);
      $statement->bindValue(':token', $data[0]);
      $statement->execute();
    } catch (PDOException $error) {
      return messageError($error->getMessage(), 50);
    }

    return outputData($sessionToken);
  } else {
    return outputData(NULL);
  }
}

function model($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(messageError('Неверный тип запроса', 10));

    return false;
  }

  $param = json_decode(file_get_contents('php://input'));
  $nameUser = htmlspecialchars($param->name) ?? '';
  $password = md5(htmlspecialchars($param->password)) ?? '';

  if (!$nameUser) {
    echo json_encode(messageError('Отсутствует имя пользователя', 20));

    return false;
  }

  if (!$password) {
    echo json_encode(messageError('Отсутствует пароль', 20));

    return false;
  }

  echo json_encode(authorization($main, $nameUser, $password));

  return true;
}

model($main);
