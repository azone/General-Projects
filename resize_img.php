<?php
$img = $_GET['img'];
$w = $_GET['w'] ? $_GET['w'] : 100;
$h = $_GET['h'] ? $_GET['h'] : 100;

$im = @imagecreatefromstring(file_get_contents($img));

if(!$im)
{
  exit;
}

$im_resized = imageCreate($w, $h);
$white_color = imagecolorallocate($im_resized, 255, 255, 255);
imagefill($im_resized, 0, 0, $white_color);

$old_w = imagesx($im);
$old_h = imagesy($im);
$old_rate = $old_w / $old_h;

if($old_rate == $new_rate)
{
  imageCopyResized($im_resized, $im, 0, 0, 0, 0, $w, $h, $old_w, $old_h);
}
else
{
  if($old_w > $old_h)
  {
    $tmp_h = $h;
    $tmp_w = $tmp_h * $old_rate;
    $tmp_im = imageCreate($tmp_w, $tmp_h);
    $white_color = imagecolorallocate($tmp_im, 255, 255, 255);
    imagefill($tmp_im, 0, 0, $white_color);

    imageCopyResized($tmp_im, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $old_w, $old_h);
    imageCopyResized($im_resized, $tmp_im, 0, 0, round(($tmp_w - $w) / 2), 0, $w, $h, $w, $h);
    imagedestroy($tmp_im);
  }
  else
  {
    $tmp_w = $w;
    $tmp_h = $tmp_w / $old_rate;
    $tmp_im = imageCreate($tmp_w, $tmp_h);
    $white_color = imagecolorallocate($tmp_im, 255, 255, 255);
    imagefill($tmp_im, 0, 0, $white_color);

    imageCopyResized($tmp_im, $im, 0, 0, 0, 0, $tmp_w, $tmp_h, $old_w, $old_h);
    imageCopyResized($im_resized, $tmp_im, 0, 0, 0, round(($tmp_h - $h) / 2), $w, $h, $w, $h);
    imagedestroy($tmp_im);
  }
}

header('Content-Type: image/jpeg');
imagejpeg($im_resized);
imagedestroy($im);
imagedestroy($im_resized);
?>
