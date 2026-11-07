<?php

function getInternalToken($main, $sessionToken)
{
  try {
    $sql = 'SELECT * FROM users WHERE sessionToken LIKE :sessionToken LIMIT 1';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':sessionToken', $sessionToken);
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }

  $data = [];
  while ($item = $statement->fetch(\PDO::FETCH_ASSOC)) {
    $data[] = [
      'token' => $item['token'],
      'id' => $item['users_id'],
    ];
  }

  if(empty($data[0])) return NULL;

  if ($data[0]['token']) {
    return $data[0];
  } else {
    return NULL;
  }
}
