<?php
require_once '../index.php';
require_once '../messageError.php';

$main = new Main();

function model($main, $token, $captcha)
{
  try {
    $sql = 'DELETE FROM captcha WHERE ts < now() - interval 60 second';
    $statement = $main->PDO->prepare($sql);
    $statement->execute();

    $sql = 'INSERT INTO captcha (captcha_id, token, code) VALUES (:captcha_id, :token, :code)';
    $statement = $main->PDO->prepare($sql);
    $statement->bindValue(':captcha_id', NULL);
    $statement->bindValue(':token', $token);
    $statement->bindValue(':code', md5($captcha));
    $statement->execute();
  } catch (PDOException $error) {
    return messageError($error->getMessage(), 50);
  }
}

function captcha($main)
{
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    return false;
  }

  $token = htmlspecialchars($_GET['token']) ?? '';

  $letters = 'ABCDEFGKIJKLMNOPQRSTUVWXYZ';
  $caplen = 6;
  $width = 120;
  $height = 20;
  $font = $_SERVER['DOCUMENT_ROOT'] . '/todo/captcha/fonts/ComicSansMS.woff';
  $fontsize = 14;
  $im = imagecreatetruecolor($width, $height);

  imagesavealpha($im, true);

  $bg = imagecolorallocatealpha($im, 0, 0, 0, 127);

  imagefill($im, 0, 0, $bg);
  putenv('GDFONTPATH=' . realpath('.'));

  $captcha = '';

  for ($i = 0; $i < $caplen; $i++) {
    $captcha .= $letters[rand(0, strlen($letters) - 1)];
    $x = ($width - 20) / $caplen * $i + 10;
    $x = rand($x, $x + 4);
    $y = $height - (($height - $fontsize) / 2);
    $curcolor = imagecolorallocate($im, rand(0, 100), rand(0, 100), rand(0, 100));
    $angle = rand(-25, 25);
    imagettftext($im, $fontsize, $angle, $x, $y, $curcolor, $font, $captcha[$i]);
  }

  if ($token && $captcha) {
    model($main, $token, $captcha);
  }

  imagepng($im);
  imagedestroy($im);
}

captcha($main);
