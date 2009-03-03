Ett exempel på en enkel xml-baserad backend för bildspel. Den mesta funktionaliteten finns implementerad i includes/img.php.

Installation
===========================

Innan du testar detta så måste du skapa följande tabell:

CREATE TABLE image(
  id INT NOT NULL AUTO_INCREMENT,
  slide VARCHAR(100) NOT NULL DEFAULT '',
  name VARCHAR(100) NOT NULL DEFAULT '',
  filename VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  PRIMARY KEY(id),
  INDEX idx_slide(slide)
);

...och ställa in dina egna anslutningsuppgifter i funktionen img_connect() i includes/img.php

Användning
===========================

Exemplet hanterar multipla bildspel och de olika bildspelen kan administreras genom att ange en underkatalog till admin.

För att administrera bildspelet "special" går du till URL:en /admin/special. För att administrera standardbildspelet går du till /admin.

XML:en skrivs ut när man går till /. Det nuvarande formatet är:

<?xml version="1.0" encoding="utf-8"?>
<images>
  <image name="Bildnamn" src="bildurl">
    <description>Beskrivning på bilden</description>
  </image>
</images>

...detta kan du redigera i funktionen img_xml() i includes/img.php.

För att få XML:en för andra bildspel går du till /bildspelsnamn. Så för XML:en för bildspelet "special" skall url:en /special anges.