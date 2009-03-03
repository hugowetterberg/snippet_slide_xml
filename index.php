<?php
/**
 * @file
 *  Skriver ut ett xml-dokument med information om bilderna
 */

// Inkludera img-koden och berätta var bilderna skall sparas
// och vad url:en till katalogen är.
require('includes/img.php');
if ($_GET['slide']) {
  img_config('slide', $_GET['slide']);
}
img_config('image_directory', dirname(__FILE__) . '/images');
img_config('image_directory_url', 'http://' . $_SERVER['HTTP_HOST'] . '/images');

// Berätta att vi skickar xml
header('Content-type: text/xml');
// Skriv ut xml-dokumentet
print img_xml();
