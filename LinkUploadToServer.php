<html>
<form method="post">
<input name="url" size="50" />
<input name="submit" type="submit" />
</form>
<?php
    // maximum execution time in seconds
    set_time_limit (24 * 60 * 60);

    if (!isset($_POST['submit'])) die();

    // folder to save downloaded files to. must end with slash
    $destination_folder = '';

    $url = $_POST['url'];
    $newfname = $destination_folder . basename($url);

    $file = fopen ($url, "rb");
    if ($file) {
      $newf = fopen ($newfname, "wb");

      if ($newf)
      while(!feof($file)) {
        fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
      }
    }

    if ($file) {
      fclose($file);
    }

    if ($newf) {
      fclose($newf);
    }
?>
</html>