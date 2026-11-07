<?php
require_once 'index.php';
require_once 'generateToken.php';
require_once 'messageError.php';
require_once 'outputData.php';

$main = new Main();

function registration($main, $nameUser, $password, $tokenRegistration)
{
  try {
    $sql = 'INSERT INTO users (users_id, token, name, password) VALUES (:users_id, :token, :name, :password)';
    $statement  =  $main->PDO->prepare($sql);
    $statement->bindValue(':users_id', NULL);
    $statement->bindValue(':token', generateToken($password));
    $statement->bindValue(':name', $nameUser);
    $statement->bindValue(':password', $password);
    $statement->execute();

    $sql = 'DELETE FROM captcha WHERE token = :tokenRegistration';
    $statement  =  $main->PDO->prepare($sql);
    $statement->bindValue(':tokenRegistration', $tokenRegistration);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  return outputData(true);
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
  $tokenRegistration = htmlspecialchars($param->tokenRegistration) ?? '';

  if (!$nameUser) {
    echo json_encode(messageError('Отсутствует имя пользователя', 20));

    return false;
  }

  if (!$password) {
    echo json_encode(messageError('Отсутствует пароль', 20));

    return false;
  }

  echo json_encode(registration($main, $nameUser, $password, $tokenRegistration));

  return true;
}

model($main);
