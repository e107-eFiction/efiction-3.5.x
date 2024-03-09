<?php


$defaulttitles = array("welcome" => "Begrüßung", "copyright" => "Copyright-Footer", "printercopyright" => "Druckercopyright", 
"help" => "Hilfe", "rules" => "{sitename} Schreibregeln", "thankyou" => "{sitename} Annahmebestätigung", "nothankyou" => "Ablehnungsmitteilung", 
"tos" => "Nutzungsbestimmungen");

define("_ALTER11CHARACTERS", "Bearbeite Tabelle fanfiction_characters");
define("_ALTER11CATEGORIES", "Bearbeite Tabelle fanfiction_categories");
define("_ALTER11RATINGS", "Bearbeite rating-Tabelle.");
define("_DONE", "Erledigt!");
define("_ERROR_CONFIGWRITE", "Fehler beim Schreiben in config.php! Konnte Datei nicht öffnen.");
define("_CONFIG_WRITTEN", "Konfigurationsdatei wurde geschrieben.");
define("_MANUAL2", "Manuell");
define("_ADMINACCT", "Admin-Account einrichten");
define("_AUTO", "Automatisch");
define("_PAGE", "Seite");
define("_BLOCK", "Block");
define("_MESSAGE", "Meldung");
define("_RESULT", "Ergebnis");
define("_FIELD", "Feld");
define("_AUTHORFIELDS", "Felder für Autorenprofil installieren");
define("_FIELDDATAINFO", "In diesem Schritt werden die Standardoptionen für die Felder des Autorenprofils installiert. Du kannst später im Adminbereich die Felder bearbeiten, löschen oder neue hinzufügen.");
define("_FIELDUPDATE", "Falls du schon andere als die oben aufgelisteten Felder deiner Seite hinzugefügt hast, kannst du hier wenn gewünscht die Tabelle fanfiction_authorfields manuell bearbeiten, bevor du mit der Installation fortfährst. Im nächsten Schritt werden dann die Einstellungen der Tabelle fanfiction_authors in das neue Format übertragen. Stelle auch sicher, dass die Feldnamen übereinstimmen.");
define("_FIELDMANUAL", "Bitte jetzt die Felddaten manuell mit Hilfe der .sql-Dateien im docs-Ordner installieren.");
define("_FIELDAUTOFAIL", "Falls ein Feld nicht installiert werden konnte, installiere es bitte manuell mit Hilfe der authorfields.sql im Docs-Ordner.");
define("_AUTHORUPDATE", "Autor-Einstellungstabelle füllen");
define("_AUTHORUPDATEINFO", "In diesem Schritt werden die Tabellen fanfiction_authorprefs und fanfiction_authorinfo mit Informationen aus der Autoren-Tabelle versorgt.");
define("_AUTHORRESULT", "Autoren-Einstellungen verschoben.");
define("_AUTHORDROPRESULT", "Spalten aus Autoren-Tabelle gelöscht.");
define("_BLOCKDATA", "Blockdaten installieren");
define("_BLOCKDATAUPGRADE", "In diesem Schritt werden die Blockdaten von 2.0 in die Datenbank verschoben. Diese Einstellungen kannst du später jederzeit im Blöcke-Interface des Adminbereiches ändern.");
define("_BLOCKDATANEW", "In diesem Schritt werden die Standard-Blockdaten installiert. Du kannst diese Einstellungen später jederzeit im Blöcke-Interface deines Adminbereiches ändern.");
define("_BLOCKDATAFAILUPGRADE", "Falls ein Block nicht installiert werden konnte, benutze die blocks.sql im docs-Ordner, um die fehlenden Informationen manuell zu installieren.");
define("_BLOCKDATAMANUAL", "Bitte jetzt Die Blockdaten mit Hilfe der blocks.sql im Docs-Ordner manuell installieren.");
define("_CHALLENGESALTER", "Spalte 'responses' zur Geschichten-Tabelle hinzugefügt.");
define("_CHALLENGESETTING", "Spalte 'anonchallenges' zur Einstellungstabelle hinzugefügt.");
define("_CHALLENGEUPDATE", "Challenge-Daten aktualisieren");
define("_CHALLENGEUPDATEINFO", "In diesem Schritt wird die Challenges-Tabelle aktualisiert, um eine neue Spalte für Antworten hinzuzufügen und die Daten für Anzal der Antworten.");
define("_CHALLENGESEMPTY", "Keine Challenges gefunden, das Challenges-Modul wird nicht installiert. Falls du es später verwenden möchtest, kannst du das Modul mit Hilfe der install.php im Ordner modules/challenges/ nachinstallieren.");
define("_CONFIG1DETECTED", "eFiction 1.1 Konfigurationsdatei gefunden.<br /><a href='upgrade11.php'>Weiter mit Upgrade.</a>");
define("_CONFIG2DETECTED", "eFiction 2.0 Konfigurationsdatei gefunden.<br /><a href='upgrade20.php'>Weiter mit Upgrade.</a>");
define("_CONFIGFAILED", "Konnte nicht zur Datenbank verbinden. Die von dir angegebenen Informationen scheinen falsch zu sein. Bitte versuche es erneut.");
define("_CONFIGDATA", "Setup der Konfigurationsdatei");
define("_CONFIGSUCCESS", "Konfigurationsdatei wurde geschrieben.");
define("_CONTINUE", "Weiter");
define("_DBHOST", "Datenbank-Host:");
define("_DBNAME", "Datenbankname:");
define("_DBUSER", "Datenbankbenutzer:");
define("_DBPASS", "Datenbankpasswort:");
define("_DROPGENRES", "lösche Genre-Tabelle");
define("_DROPWARNINGS", "lösche warnings-Tabelle");
define("_DROPGWSTORIES", "'wid' und 'gid' aus Geschichten-Tabelle gelöscht.");
define("_DROPGWSERIES", "'wid' und 'gid' aus Serien-Tabelle gelöscht.");
define("_FAVUPDATE", "AAktualisiere Favoritentabelle");
define("_FAVUPDATEINFO", "In diesem Schritt werden die Favoriten-Informationen in die neue Favoritentabelle verschoben und die alten favstor-, favseries- und favauth-Tabellen entfernt.");
define("_FAVUPDATEINFO11", "In diesem Schritt werden die Favoriten-Informationen in die neue Tabelle verschoben und die alten favstor- und favauth-Tabellen entfernt.");
define("_FAV1", "Informationen für Lieblingsgeschichten verschoben.");
define("_FAV2", "Informationen für Lieblingsserien verschoben.");
define("_FAV3", "Informationen für Lieblingsautoren verschoben.");
define("_FAV4", "Tabelle favstor gelöscht.");
define("_FAV5", "Tabelle favseries gelöscht.");
define("_FAV6", "Tabelle favauth gelöscht.");
define("_INSTALLTABLES", "Tabellen installieren");
define("_LINKDATA", "Daten für Navigationslinks installieren");
define("_LINKDATAINFO", "In diesem Schritt werden die Standardlinks installiert. Du kannst sie später jederzeit im Admin-Interface bearbeiten.");
define("_LINKMANUAL", "Bitte die Daten mit Hilfe der pagelinks.sql im Ordner Docs jetzt manuell installieren.");
define("_LINKSETUP", "Wie möchtest du die Daten der Seitenlinks installieren?");
define("_LINKAUTOFAIL", "Falls ein Link nicht installiert werden konnte, benutze jetzt bitte die pagelinks.sql aus dem Docs-Ordner, um die Informationen manuell zu installieren.");
define("_MESSAGEDATA", "Daten für Meldungen");
define("_MESSAGEMANUAL", "Bitte jetzt die Meldungen aus der messages.sql im Docs-Ordner installieren.");
define("_MESSAGEDATAUPGRADE", "In diesem Schritt werden die Standardmeldungen deiner Seite in die Datenbank verschoben. Du kannst die Meldungen später jederzeit im Adminbereich deiner Seite ändern.");
define("_MESSAGEDATANEW", "In diesem Schritt werden die Standardmeldungen deiner Seite installiert. Du kannst die Meldungen später jederzeit im Adminbereich deiner Seite ändern.");
define("_MESSAGEAUTOFAILUPGRADE", "Falls eine der Meldungen nicht installiert werden konnte, kannst du sie mit Hilfe der messages.sql im Docs-Ordner manuell installieren oder sie später im Adminbereich eintragen.");
define("_MISCDBUPDATE", "Verschiedene Datenbank-Updates");
define("_MISCDBUPDATEINFO", "In diesem Schritt werden verschiedene Felder der Tabellen bearbeitet, um die Datenbank kleiner zu machen. Wie möchtest du fortfahren?");
define("_MISCDBUPDATEMANUAL", "Du kannst jetzt die Informationen aus der Datei docs/upgrade11_step18.sql benutzen, um die Tabellen manuell zu bearbeiten.");
define("_MOVECLASSES", "Genres und Warnings verschieben");
define("_MOVECLASSESINFO", "In diesem Schritt werden die Informationen für Genres und warnings in die neue Klassifizierungs-Tabelle verschoben.");
define("_MOVECLASSFAIL", "Falls eine der Warnings oder Genres nicht verschoben werden konnte, tue dies bitte jetzt manuell.");
define("_NEWSUPDATE", "News-Kommentare aktualisieren");
define("_NEWSUPDATEINFO", "In diesem Schritt wird das Feld 'uname' der Kommentartabelle in 'uid' geändert.");
define("_NEWSUPDATERESULT", "News-Kommentare aktualisiert.");
define("_NEWTABLESSETUP", "Wie sollen die neuen Tabellen installiert werden?");
define("_NEWTABLESMANUAL", "Bitte installiere die Tabellen jetzt mit Hilfe der upgrade20_step4.sql im Docs-Ordner manuell.");
define("_NEWTABLEAUTOFAIL", "Falls eine Tabelle nicht installiert werden konnte, benutze bitte jetzt die upgrade20_step4.sql im Docs-Ordner, um sie manuell zu installieren.");
define("_NEWTABLEAUTOFAIL11", "Falls eine Tabelle nicht installiert werden konnte, benutze bitte jetzt die upgrade11_step4.sql im Docs-Ordner, um sie manuell zu installieren.");
define("_PANELDATA", "Panel-Daten installieren");
define("_PANELDATAINFO", "In diesem Schritt werden die Standardeinstellungen der Panels für den Adminbereich, Benutzerbereich, Top-10-Listen und der Benutzerprofilseite installiert. Du kannst die Konfiguration später jederzeit im Adminbereich ändern.");
define("_PANELSETUP", "Wie möchtest du die Paneldaten installieren?");
define("_PANELMANUAL", "Bitte jetzt die Paneldaten mit Hilfe der panels.sql im Docs-Ordner manuell installieren.");
define("_PANELAUTOFAIL", "Falls ein Panel nicht installiert werden konnte, installiere es bitte mit Hilfe der Datei panels.sql im Docs-Ordner manuell.");
define("_REVIEWUPDATE", "Review-Tabelle und Daten aktualisieren");
define("_REVIEWALTER1", "Spalte 'sid' in 'item' geändert");
define("_REVIEWALTER2", "Spalte 'type' hinzugefügt");
define("_REVIEWALTER3", "Spalte 'member' in 'uid' geändert");
define("_REVIEWALTER4", "Spalte 'seriesid' gelöscht");
define("_REVIEWALTER5", "Spalte 'respond' hinzugefügt");
define("_REVIEWALTER6", "Spalte 'sid' in 'chapid' geändert");
define("_REVIEWALTER7", "Spalte 'psid' in 'item' geändert");
define("_REVIEWUPDATE1", "Typ für alle Geschichtenreviews auf 'ST' setzen.");
define("_REVIEWUPDATE2", "Set item to seriesid and type to 'SE' for all series reviews.");
define("_REVIEWUPDATE3", "Alle beantworteten Reviews als beantwortet markieren.");
define("_REVIEWUPDATEINFO", "In diesem Schritt wird die Review-Tabelle bearbeitet und die Reviews in das neue Format konvertiert.");
define("_SERIESREVIEWS", "Serien-Reviews aktualisieren");
define("_SERIESREVIEWSINFO", "In diesem Schritt werden die Serien-Reviews und Bewertungen aktualisiert, um die Reviews und Bewertungen zu beinhalteten Geschichten und Serien einzubeziehen.");
define("_SETTINGSPREFIX", "Präfix der Settings-Tabelle:");
define("_SETTINGSTABLE", "Settings-Tabelle erstellen"); // Added 01/15/07
define("_SETTINGSTABLEMANUAL", "Bitte jetzt die Settings-tabelle mit Hilfe der tables.sql im Docs-Ordner manuell installieren.");
define("_SETTINGSTABLENOTE", "Du kannst unabhängig von den anderen Tabellen ein Präfix für die Einstellungstabelle angeben. Dies erlaubt es, mehrere Instanzen von eFiction in der selben Datenbank mit den gleichen Einstellungen zu betreiben.");
define("_SETTINGSTABLESETUP", "Wie möchtest du die Settings-Tabelle installieren?");
define("_SETTINGSTABLESUCCESS", "Settings-Tabelle erfolgreich erstellt!");
define("_SETTINGSTABLEAUTOFAIL", "Die automatische Erstellung der Settings-Tabelle ist fehlgeschlagen! Bitte erstelle sie manuell.");
define("_SITESETTINGSMOVED", "Die Seiteneinstellungen wurden in die Settings-Tabelle verschoben.");
define("_SITEKEYNOTE", "Du kannst den zufällig generierten Key benutzen oder deinen eigenen erstellen.");
define("_SKINWARNING", "<strong>Hinweis:</strong> Damit eFiction 3.0 korrekt dargestellt wird, müssen einige Änderungen an deinen Skins vorgenommen werden. Du solltest einen der im Download enthaltenen Skins benutzen, bis du deine eigenen Skins aktualisiert hast.");
define("_STORIESPATHNOTWRITABLE", "Der Ordner, in welchen du deine Geschichten speichern möchtest, hat keine Schreibrechte! Du musst die Berechtigung auf 777 (auf manchen Systemen auch 755) setzen, um Geschichten in diesem Ordner zu speichern!");
define("_STORIESUPDATED", "Die Geschichten wurden aktualisiert.");
define("_TABLESMANUAL", "Bitte jetzt die Tabellen aus dem Docs-Ordner manuell installieren.");
define("_TABLESINSTALL", "Datenbanktabellen installieren.");
define("_TABLEFAILED", "Falls eine der Tabellen nicht installiert werden konnte, installiere sie jetzt bitte manuell mit Hilfe der tables.sql im Ordner Docs.");
define("_UPDATECATORDER", "Kategorie-Anordnung aktualisieren");
define("_UPDATECATORDERINFO", "<strong>Hinweis:</strong> Dies hat keinen Einfluss auf die Anzeige der Kategorien am Bildschirm, sondern aktualisiert lediglich die Anhordnung in der Datenbank.");
define("_UPDATESTORIES", "Geschichten- und Serieninformationen aktualisieren");
define("_UPDATESTORIES11", "Geschichten aktualisieren");
define("_UPDATESTORIES11INFO", "In diesem Schritt werden deine Geschichten aktualisiert und einige der Felder in der Tabelle bearbeitet. Dieser Vorgang wird in Stapeln von je 200 Aktionen abgearbeitet, die Bearbeitung der Tabelle zum Schluss.");
define("_UPDATESTORIESINFO", "In diesem Schritt werden die Daten deiner Geschichten aktualisiert. Die Namen von Genres, Charakteren und Warnings werden in ID-Nummern umgewandelt. Dieser Vorgang wird in Stapeln von je 200 Geschichten abgearbeitet.");
define("_UPDATESTORIESTABLE", "Geschichten- und Serientabellen aktualisieren");
define("_UPDATESTORIESTABLEINFO", "In diesem Schritt werden die Klassifizierungsspalten in deiner Geschichten- und Serientabelle erstellt. Wie möchtest du weitermachen?");
define("_UPDATESTORIESTABLEMANUAL", "Bitte jetzt die Tabellen manuell mit Hilfe der upgrade20_step10.sql im Docs-Ordner aktualisieren.");
define("_UPDATESTORIESTABLE11", "Geschichtentabelle aktualisieren");
define("_UPDATESTORIESTABLEINFO11", "In diesem Schritt wird die Geschichtentabelle modifiziert, um mit eFiction 3.0 konform zu sein. Wie möchtest du weitermachen?");
define("UPDATESTORIESTABLEMANUAL11", "Bitte jetzt die Geschichtentabelle mit Hilfe der Datei upgrade11_step12.sql im Docs-Ordner aktualisieren.");
define("_UPGRADEEND","Du kannst nun die folgenden Dateien und Ordner löschen:<br />
<ul style='text-align: left !important; width: 60%; margin: 1em auto;'>
<li>Ordner messages/</li>
<li>dbconfig.php sowie den Ordner der Datenbankkonfigurationsdatei </li>
<li>blocks_config.php</li>
<li>categories.php (Wird durch browse.php verarbeitet)</li>
<li>formselect.js</li>
<li>func.naughty.php</li>
<li>func.pagemenu.php</li>
<li>func.ratingpics.php</li>
<li>functions.php</li>
<li>func.reviewform.php</li>
<li>help.php (Ist jetzt in der Datenbank gespeichert)</li>
<li>javascript.js (eine neue Version befindet sich im Ordner includes)</li>
<li>Ordner lib/ </li>
<li>members_list.php (Eine neue Version befindet sich im Ordner includes)</li>
<li>naughtywords.php</li>
<li>seriesblock.php (Eine neue Version befindet sich im Ordner includes)</li>
<li>storyblock.php (Eine neue Version befindet sich im Ordner includes)</li>
<li>submission.php (Ist jetzt in der Datenbank gespeichert)</li>
<li>timefunctions.php</li>
<li>titles.php (Wird durch browse.php verarbeitet)</li>
<li>tos.php (Ist jetzt in Datenbank gespeichert)</li>
<li><strong> Ordner install</strong></li>
</ul>
<p>Du solltest außerdem aus Sicherheitsgründen die Rechte der Datei config.php auf 644 setzen.</p>
<p><a href='../index.php'>Zurück zu deiner Seite.</a></p>");

define("_UPGRADE11END", "Du kannst nun die folgenden Dateien und Ordner löschen:<br />
<ul style='text-align: left !important; width: 60%; margin: 1em auto;'>
<li>adminfunctions.php</li>
<li>adminheader.php</li>
<li>adminnews.php</li>
<li>adminstories.php</li>
<li>adminusers.php</li>
<li>blocks.php</li>
<li>categories.php (Wird durch browse.php verarbeitet)</li>
<li>functions.php</li>
<li>help.php (Ist jetzt in der Datenbank gespeichert)</li>
<li>javascript.js (Eine neue Version befindet sich im Ordner includes)</li>
<li>langadmin.php</li>
<li>languser.php</li>
<li>Ordner lib/</li>
<li>phpinfo.php <strong>(Stellt ein Sicherheitsrisiko dar!)</strong></li>
<li>storyblock.php (Eine neue version befindet sich im Ordner includes)</li>
<li>timefunctions.php</li>
<li>titles.php (Wird durch browse.php verarbeitet)</li>
<li>Ordner install/ <strong>(Stellt ein Sicherheitsrisiko dar!)</strong></li>
<li>Dein Ordner mit dbconfig.php</li>
</ul><p><a href='../index.php'>Zurück zu deiner Seite.</a></p>
<p>Du solltest aus Sicherheitsgründen die Rechte der Datei config.php auf 644 setzen.</p>
<p><a href='../index.php'>Zurück zu deiner Seite.</a></p>");
define("_HELP_DBHOST", "Der Host-Server deiner Datenbank. Falls dir dein Webhoster keine anderen Daten gegeben hat, ist dies in den meisten Fällen localhost.");
define("_HELP_DBNAME", "Dies ist der Name deiner Datenbank. Du musst sie vorher erstellt haben, um mit der Installation fortfahren zu können.");
define("_HELP_DBUSER", "Dies ist der Benutzer mit Zugriff auf die Datenbank.");
define("_HELP_DBPASS", "Dies ist das Passwort des Datenbankbenutzers.");
define("_HELP_INSTALL_SITEKEY", "Der Site-Key wird für den Zugriff auf die Seiteneinstellungen benutzt und verhindert Konflikte bei mehreren Installationen auf einer Domain. Du <strong>musst</strong> für jede eFiction-Installation einen <strong>eindeutigen</strong> Site-Key angeben! Wenn du das Feld leer lässt, wird das Script einen zufälligen Key generieren.");
define("_HELP_SETTINGSPREFIX", "Du kannst unabhängig von den anderen Tabellen ein Präfix für die Einstellungstabelle angeben. Dies erlaubt es, mehrere Instanzen von eFiction in der selben Datenbank mit den gleichen Einstellungen zu betreiben.");

//  Missing LANs - fatal error  
define("_EMAILREQUIRED", "Email is required");
define("_BADSITEKEY", "Wrong Sitekey");
