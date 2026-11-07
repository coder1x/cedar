<?php

function outputData($data)
{
  return [
    'error' => false,
    'messageError' => '',
    'value' => $data ?? NULL,
  ];
}
