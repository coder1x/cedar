<?php

function messageError($message, $code)
{
  return [
    'error' => true,
    'messageError' => $message,
    'code' => $code,
  ];
}
