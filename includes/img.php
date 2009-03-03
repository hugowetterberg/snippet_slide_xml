<?php

/*
Förutsätter att följande tabell finns:

CREATE TABLE image(
  id INT NOT NULL AUTO_INCREMENT,
  slide VARCHAR(100) NOT NULL DEFAULT '',
  name VARCHAR(100) NOT NULL DEFAULT '',
  filename VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  PRIMARY KEY(id),
  INDEX idx_slide(slide)
);
*/

/**
 * Sätter eller hämtar ett konfigurationsvärde.
 */
function img_config($key, $value=NULL) {
  static $config = array();

  if ($value!==NULL) {
    $config[$key] = $value;
  }
  if (isset($config[$key])) {
    return $config[$key];
  }
  return NULL;
}

/**
 * Hjälpfunktion för att hämta ett konfigurationsvärde med ett default-värde.
 */
function img_config_get($key, $default) {
  $val = img_config($key);
  return $val === NULL ? $default : $val;
}

/**
 * Ansluter till mysqlservern
 */
function img_connect($supress_error=FALSE) {
  static $mysql = NULL;

  if ($mysql===NULL) {
    // TODO: Ändra detta till dina egna anslutningsuppgifter
    $server = "localhost";
    $user = "användarnamn";
    $pass = "ditt lösenord";
    $database = "imagedb";
    $mysql = @new mysqli($server, $user, $pass, $database);

    if ($mysql->connect_error) {
      if (!$supress_error) {
        img_message(sprintf('Kunde inte ansluta till databasen: %s', $mysql->connect_error), 'error');
      }
      return FALSE;
    }
    else {
      $mysql->query("SET NAMES 'utf8'");
    }
  }

  return $mysql;
}

/**
 * Visar ett formulär för att ladda upp bilder
 */
function img_form() {
  if (img_connect()) {
    img_save_file();

    // Visa formuläret
    require('img_form.inc');
  }
}

/**
 * Sparar en uppladdad bild i filsystemet och databasen
 */
function img_save_file() {
  if (!$mysql=img_connect()) {
    return;
  }
  
  $dir = img_config('image_directory');
  if (is_writable($dir)) {
    if (isset($_FILES['image'])) {
      $file = $_FILES['image'];
      $slide = img_config_get('slide', '');

      // Se till att filnamnet bara innehåller små bokstäver
      $filename = mb_strtolower($_FILES['image']['name'], 'utf-8');
      // Ersätt ö med o
      $filename = preg_replace('/ö/', 'o', $filename);
      // Ersätt å och ä med a
      $filename = preg_replace('/å|ä/', 'a', $filename);
      // Ersätt allt som inte är a-z, punkt eller _ med _
      $filename = preg_replace('/[^a-z._]/', '_', $filename);

      $path = $dir . '/' . $filename;

      // Kolla så att filen inte redan finns
      if (file_exists($path)) {
        img_message(sprintf('Kunde inte spara "%s", det finns redan en fil med det namnet', $filename), 'error');
        return;
      }
      // Spara den uppladdade filen i katalogen
      move_uploaded_file($_FILES['image']['tmp_name'], $path);

      // Spara information om bilden
      $query = $mysql->prepare("INSERT INTO image(slide, name, filename, description) VALUES(?,?,?,?)");
      $query->bind_param('ssss', $slide, $_POST['name'], $filename, $_POST['description']);
      $query->execute();
      $query->close();

      img_message(sprintf('Sparade bilden "%s" som "%s"', $_POST['name'], $filename));
    }
  }
  else {
    img_message(sprintf('Kan inte skriva till katalogen %s', $dir), 'error');
  }
}

/**
 * Visar ett formulär för borttagning av bilder
 */
function img_delete_form() {
  if (img_connect()) {
    if ($_POST['delete']) {
      // Ta bort bilden om formuläret har skickats
      img_delete_image($_POST['delete']);
    }

    // Visa formuläret
    $images = img_get_images();
    require('img_delete_form.inc');
  }
}

/**
 * Tar bort en bild från databasen och filsystemet
 */
function img_delete_image($id) {
  if (!$mysql = img_connect()) {
    return;
  }
  
  $dir = img_config('image_directory');

  $query = $mysql->prepare("SELECT name, filename FROM image WHERE id=?");
  $query->bind_param('i', $id);
  $query->execute();

  $query->bind_result($name, $filename);
  if ($query->fetch()) {
    $query->close();

    // Ta bort bildfilen
    unlink($dir . '/' . $filename);

    // Ta bort bilden från databasen
    $del_query = $mysql->prepare("DELETE FROM image WHERE id=?");
    $del_query->bind_param('i', $id);
    $del_query->execute();
    $del_query->close();

    img_message(sprintf('Tog bort bilden "%s"', $name));
  }
  else {
    img_message('Bilden var redan borttagen');
  }
}

/**
 * Skriver ut ett fel- eller statusmeddelande
 */
function img_message($msg, $type='notice') {
  print '<p class="' . $type . '">' . $msg . '</p>';
}

/**
 * Skapar ett xml-dokument med bildinformation
 */
function img_xml() {
  $images = img_get_images(TRUE);

  $url = img_config('image_directory_url');

  // Skapa ett xml-dokument med root-elementet images
  $doc = new DomDocument('1.0', 'utf-8');
  $e_images = $doc->appendChild($doc->createElement('images'));

  foreach ($images as $image) {
    $e_image = $e_images->appendChild($doc->createElement('image'));

    // Sätt namn och källatribut
    $e_image->setAttribute('name', $image['name']);
    $e_image->setAttribute('src', $url . '/' . $image['filename']);

    // Lägg till ett element med beskrivningen
    $e_description = $e_image->appendChild($doc->createElement('description'));
    $e_description->appendChild($doc->createTextNode($image['description']));
  }

  // Slå på indentering av xml:en och returnera dokumentet som en sträng
  $doc->formatOutput = TRUE;
  return $doc->saveXML();
}

/**
 * Bildspelsnamn från databasen
 */
function img_get_slides($supress_errors=FALSE) {
  $slides = array();

  if (!$mysql=img_connect($supress_errors)) {
    return $slides;
  }

  $query = $mysql->prepare("SELECT DISTINCT slide FROM image");
  if ($query->execute()) {
    $query->bind_result($slide);
    while ($query->fetch()) {
      // Hämta resultaten från databasen
      $slides[] = $slide;
    }
  }
  else if (!$supress_errors) {
    img_message(sprintf('Kunde inte hämta bildspelsnamn: %s', $query->error), 'error');
  }

  return $slides;
}

/**
 * Hämtar bild-information från databasen
 */
function img_get_images($supress_errors=FALSE) {
  $images = array();

  if (!$mysql=img_connect($supress_errors)) {
    return $images;
  }

  $slide = img_config_get('slide', '');

  $query = $mysql->prepare("SELECT id, name, filename, description FROM image WHERE slide=?");
  $query->bind_param('s', $slide);
  if ($query->execute()) {
    $query->bind_result($id, $name, $filename, $description);
    while ($query->fetch()) {
      // Hämta resultaten från databasen
      $images[] = array(
        'id' => $id,
        'name' => $name,
        'filename' => $filename,
        'description' => $description,
      );
    }
  }
  else if (!$supress_errors) {
    img_message(sprintf('Kunde inte hämta bilder: %s', $query->error), 'error');
  }

  return $images;
}