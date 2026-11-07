<?php
function generateToken($password)
{
  $token = hash('sha256', mt_rand(1, 1000000) . time());
  return ($token . mb_substr($password, 0, 5));
}