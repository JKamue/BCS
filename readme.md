Idee hinter der Ordnerstruktur:
```
/config           Configuration für Datenbanken und anderes
/data             Alles was gespeichert werden muss
  /cache          Cache
  /logs           Logs
    /cronjobs     Speichert Fehler in Cronjobs usw
    /team         Speichert welches Teammitglied was autorisiert hat
      /[mitglied] Unterordner für jedes Teammitglied
  /tmp            Temporäre Dateien
    /lastcw       Speichert zuletzt gescannten CW für jeden Clan
/lib              Libraries       
  /external       Externe Libraries           
  /classes        Alle Klassen
  /scripts        Kleine Scripts
/src              Eigentlicher Quelltext
  /api            Für die Api
  /cronjobs       Für die Cronjobs
  /team           Für das Team
/httpdocs          Öffentlich zugänglich (frontend)
```
    