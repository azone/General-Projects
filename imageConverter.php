<?php
  $errors = array();
  $allowed_mime = array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png');
	if($_FILES['image']['size'] > 0)
  {
    if(!is_uploaded_file($_FILES['image']['tmp_name']))
      $errors[] = '该文件不是上传的文件';

    if(!in_array($_FILES['image']['type'], $allowed_mime))
      $errors[] = '只支持JPF, GIF和PNG格式的图片文件';

    if($_FILES['image']['error'] !== UPLOAD_ERR_OK)
    {
      switch($_FILES['image']['error'])
      {
        case UPLOAD_ERR_INI_SIZE:
          $errors[] = '上传的文件超过了配置文件的指定大小';
          break;

        case UPLOAD_ERR_FORM_SIZE:
          $errors[] = '上传的文件超过了表单中指定的大小';
          break;

        case UPLOAD_ERR_PARTIAL:
          $errors[] = '文件只有部分被上传';
          break;

        case UPLOAD_ERR_NO_FILE:
          $errors[] = '没有上传任何文件';
          break;

        default:
          $errors[] = '文件上传失败';
      }
    }

    if(!in_array($_POST['image_type'], array('original', 'png', 'jpeg', 'gif')))
      $errors[] = '传入的要转换的图片格式参数不正确';
    if(isset($_POST['is_set_quality']))
    {
      if(($_POST['image_type'] == 'original' && ($_FILES['image']['type'] == 'image/jpeg' || $_FILES['image']['type'] == 'image/pjpeg' )) || $_POST['image_type'] == 'jpeg')
      {
        if($_POST['quality'] > 100 || $_POST['quality'] < 0)
          $errors[] = 'JPG图片质量必须在0和100之间';
      }

      if(($_POST['image_type'] == 'original' && $_FILES['image']['type'] == 'image/png') || $_POST['image_type'] == 'png')
      {
        if($_POST['quality'] > 9 || $_POST['quality'] < 0)
          $errors[] = 'PNG图片压缩比不须在0和9之间';
      }
    }
  }

if($_FILES['image']['size'] > 0 && !$errors)
{
  $image_path = $_FILES['image']['tmp_name'];
  $mime_type = exif_imagetype($image_path);
  $ext = image_type_to_extension($mime_type);
  switch(strtoupper(substr($ext, 1)))
  {
    case 'JPEG': case 'PJPEG': case 'JPG': default:
      $im = imageCreateFromJPEG($image_path);
      $image_create_fun = 'imageJPEG';
      break;

    case 'GIF':
      $im = imageCreateFromGIF($image_path);
      $image_create_fun = 'imageGIF';
      break;

    case 'PNG':
      $im = imageCreateFromPNG($image_path);
      $image_create_fun = 'imagePNG';
  }

  if($_POST['image_type'] !== 'original')
  {
    $image_create_fun = 'image'.$_POST['image_type'];
  }

  if($_POST['black_white'] == 'yes')
  {
    if(imageIsTrueColor($im))
    {
      imagetruecolortopalette($im, false, 256);
    }
    for($i = 0, $colors = imageColorsTotal($im); $i < $colors; $i++)
    {
      $color = imageColorsForIndex($im, $i);
      $gray = round($color['red'] * 0.229 + $color['green'] * 0.587 + $color['red'] * 0.114);
      imageColorSet($im, $i, $gray, $gray, $gray);
    }
  }
  $file_name = '';
  $extention = $_POST['image_type'];
  if($_POST['image_type'] == 'jpeg')
    $extention = 'jpg';

  if($_POST['image_type'] == 'original')
  {
    $file_name = $_FILES['image']['name'];
  }
  else
  {
    if(count(explode('.', $_FILES['image']['name'])) > 1)
    {
      $file_name = explode('.', $_FILES['image']['name']);
      array_pop($file_name);
      $file_name = implode('.', $file_name);
    }
    $file_name .= '.'.$extention;
  }

  if($_POST['show_or_save'] == 'save')
    header('Content-Disposition: attachment; filename="'.$file_name.'"');

  header('Cache-Control: no-cache, must-revalidate');

  header('Content-Type: image/jpeg');

  if(isset($_POST['interlace']))
    imageInterlace($im, 100);

  if(isset($_POST['resize']))
  {
    $scale = false;
    if(isset($_POST['is_scale']))
      $scale = true;

    resizeImage($im, $_POST['size_x'], $_POST['size_y'], $scale);
  }

  if(isset($_POST['is_set_quality']))
  {
      if($image_create_fun == 'imagejpeg' || $image_create_fun == 'imagepng')
        $image_create_fun($im, '', intval($_POST['quality']));
      else
        $image_create_fun($im);
  }
  else
  {
    $image_create_fun($im);
  }
  imageDestroy($im);
}
else
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>图片转换器</title>
  <style type="text/css">
    body
    {
      margin: 0;
      padding: 0;
      font-size: 12px;
    }

    fieldset
    {
      width: 500px;
      margin: 20px auto;
      padding: 20px;
    }

    legend
    {
      font-size: large;
      font-weight: bold;
    }

    #file
    {
      width: 400px;
    }

    #errors
    {
      margin: 20px 30px;
      border: 1px solid #F33;
      background-color: #FFC;
      color: #F33;
    }

    #errors h3
    {
      background-color: #F33;
      color: #FFC;
      margin: 0;
      padding-left: 20px;
    }

    #errors ul
    {
      list-style-type: square;
    }
  </style>

  <script type="text/javascript">
    function validateFile(elm)
    {
      var val = elm.value;
      if(!/\.(gif|jpg|png)$/.test(val.toLowerCase()))
      {
        elm.value = '';
        alert('上传的必须是GIF, JPG或者PNG图像！');

        return false;
      }

      return true;
    }

    function disableXY(elm)
    {
      var x = document.getElementById('size_x');
      var y = document.getElementById('size_y');
      var is_scale = document.getElementById('is_scale');
      if(elm.checked == true)
      {
        x.disabled = '';
        y.disabled = '';
        is_scale.disabled = '';
      }
      else
      {
        x.disabled = 'disabled';
        y.disabled = 'disabled';
        is_scale.disabled = 'disabled';
      }
    }

    window.onload = function(){ disableXY(document.getElementById('resize')); }
  </script>
</head>
<body>
  <fieldset>
    <legend>图片转换</legend>
    <?php if(count($errors) > 0): ?>
    <div id="errors">
    <h3>发生了一下错误:</h3>
    	<ul>
        <?php foreach($errors as $error): ?>
        	<li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data" method="post">
    <input type="file" id="file" name="image" onchange="validateFile(this)" />
    <div>
      转换成黑白：  <input type="radio" name="black_white" id="bw_yes" value="yes" <?php if($_POST['black_white'] == 'yes') echo 'checked="checked" '?>/><label for="bw_yes"> 是</label> <input type="radio" name="black_white" id="bw_no" value="no" <?php if($_POST['black_white'] == 'no' || !$_POST['black_white']) echo 'checked="checked" '?>/><label for="bw_no"> 否</label>
    </div>
    <div>
      转换格式：
      <input type="radio" name="image_type" id="image_type_0" value="original" <?php if(!$_POST['image_type']) echo 'checked="checked" '?>/><label for="image_type_0"> 原来的格式</label>
      <input type="radio" name="image_type" id="image_type_1" value="jpeg" <?php if($_POST['image_type'] == 'jpg') echo 'checked="checked" '?>/><label for="image_type_1"> JPG格式</label>
      <input type="radio" name="image_type" id="image_type_2" value="gif" <?php if($_POST['image_type'] == 'gif') echo 'checked="checked" '?>/><label for="image_type_2"> GIF格式</label>
      <input type="radio" name="image_type" id="image_type_3" value="png" <?php if($_POST['image_type'] == 'png') echo 'checked="checked" '?>/><label for="image_type_3"> PNG格式</label>
    </div>
    <div>
      <label for="is_set_quality">是否设置图片质量：</label><input type="checkbox" id="is_set_quality" name="is_set_quality" value="1" <?php if($_POST['is_set_quality']) echo 'checked="checked" '?>/> <input type="text" name="quality" id="quality" value="<?php echo $_POST['quality'] ? $_POST['quality'] : '100' ?>" size="3" maxlength="3" /><br />
      <em style="margin-left: 5px;">JPG图片0~100之间， PNG图片0~9之间(PNG为压缩比，0为不压缩)， 对GIF图片无效</em>
    </div>
    <div>
      <label for="interlace">是否交错：</label> <input type="checkbox" id="interlace" name="interlace" value="1" <?php if($_POST['interlace']) echo 'checked="checked" '?>/>
    </div>
    <div>
      <label for="resize">改变图片大小：</label> <input type="checkbox" id="resize" name="resize" value="1" onclick="disableXY(this)" <?php if(isset($_POST['resize'])) echo 'checked="checked" '?>/>
      <input type="text" id="size_x" name="size_x" size="4" value="<?php echo $_POST['size_x'] ?>" /> x <input type="text" id="size_y" name="size_y" size="4" value="<?php echo $_POST['size_y'] ?>" />
      <input type="checkbox" id="is_scale" name="is_scale" value="1" <?php if(isset($_POST['is_scale'])) echo 'checked="checked" '?>/> <label for="is_scale">按照比例</label>
    </div>
    <div>
      <input type="radio" name="show_or_save" id="show_or_save_show" value="show" <?php if($_POST['show_or_save'] == 'show') echo 'checked="checked" '?>/><label for="show_or_save_show"> 在浏览器显示</label> <input type="radio" name="show_or_save" id="show_or_save_save" value="save" <?php if($_POST['show_or_save'] == 'save' || !$_POST['show_or_save']) echo 'checked="checked" '?>/><label for="show_or_save_save"> 直接保存</label>
    </div>

    <input type="submit" name="submit" value=" 转换 " />
    </form>
  </fieldset>
</body>
</html>
<?php
}

function resizeImage(&$image, $width, $height, $scale = false)
{
  $tmp_image = $image;
  $original_w = imagesX($image);
  $original_h = imagesY($image);
  $tmp_w = $tmp_h = 0;
  if($scale === true)
  {
    $tmp_w = $width;
    $tmp_h = $width / $original_w * $original_h;
    if($width / $original_w * $original_h > $height)
    {
      $tmp_h = $height;
      $tmp_w = $height / $original_h * $original_w;
    }
    $width = $tmp_w;
    $height = $tmp_h;
  }

  $image = imageCreateTrueColor($width, $height);
  imageCopyResized($image, $tmp_image, 0, 0, 0, 0, $width, $height, $original_w, $original_h);
  imageDestroy($tmp_image);
}
?>