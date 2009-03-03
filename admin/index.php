<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Bildtest</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="css/img.css" type="text/css" media="screen" charset="utf-8" />
  </head>
  <body>
    <?php
      // Inkludera img-koden och berätta var bilder skall sparas
      require('../includes/img.php');
      if ($_GET['slide']) {
        img_config('slide', $_GET['slide']);
        print '<h1>Ladda upp till bildspelet ' . $_GET['slide'] . '</h1>';
      }
      else {
        print '<h1>Ladda upp till standardbildspelet</h1>';
      }
      $slides = img_get_slides();
      if (!empty($slides)) {
        print '<b>Andra bildspel: </b>';
        foreach($slides as $slide) {
          $text = empty($slide) ? 'standard' : $slide;
          print '<a href="/admin/' . $slide . '">' . $text . '</a> ';
        }
      }
      
      img_config('image_directory', dirname(dirname(__FILE__)) . '/images');

      // Visa uppladdningsformulär
      img_form();

      // Visa borttagningsformulär
      img_delete_form();
    ?>
  </body>
</html>
