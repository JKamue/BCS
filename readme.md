# BCS (Better Cwbw Stats)
Das BCS ist eine optimierte Darstellung von Minecraft Stats des Servers gommehd.net. Über eine eigens geschriebene API werden Clan- und Spielerstats abgefragt und zwischengespeichert. Später werden sie dann den Spielern detailliert ausgegeben. Das System ist derzeit unter [http://cwstats.de/](http://cwstats.de/) zu finden.

Ich habe das System vor längerer Zeit geschrieben, heute würde ich sicher vieles anders machen, jedoch ist es mit **bis zu 400 täglichen Nutzern** (beim Launch) und **~80 täglichen Benutzern** mein erfolgreichstes Projekt bisher. Derzeit umfasst BCS **356 Clans** und **nahezu 2000 Spieler** mit **mehr als 140k Spielen**.

## APIs

Für BCS gibt es zwei APIs. Die erste nenne ich GommeApi, sie parst den Quelltext diverser gommehd.net Seiten und liefert die gewünschten Informationen zurück. Die zweite API verbindet das BCS Backend mit dem Frontend, liefert also die in BCS zwischengespeicherten Daten. Beide APIs sind öffentlich zugänglich und können gerne von jedem genutzt werden.

### GommeApi
Die Enpunkte der GommeApi kann man sich hier halbwegs interaktiv selber anschauen: [https://jkamue.de/bcs/api/apicheck.php](https://jkamue.de/bcs/api/apicheck.php)

### BCS Api
Die BCS Api besitzt alle wichtigen Endpunkte um ausgewertete Informationen abzufragen.

 - Endpunkt für Clanstats, diese können alle zusammen oder einzeln abgefragt werden
 - System Statistiken, wie viele Clans, Spieler, Lineups, etc. das System umfasst
 - Suche, diese sucht nach Spieler und Clannamen, sobald mehr als 2 Zeichen gegeben sind
 - Stats eines Spielers über die minecraft uuid / Stats eines Spielers über seinen Namen (nicht zu empfehlen)
 - Endpunkt für Übersichtsdaten aller Clans
 - Ranking, die besten 10 Spieler in jeder Kategorie
 - Bewerbung, um einen Clan für die automatische Anlegung zu bewerben
 
 Gleichzeitig gibt es noch die interne Team API, sie kann über /team/api.php gefunden werden

## Anderes

Idee hinter der Ordnerstruktur (früher nur eine Notiz für mich aber lasse ich mal stehen):
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
    
#### Bei Fragen oder ähnlichem erreicht man mich gerne unter contact@jkamue.de
<p align="right">
<a href="https://twitter.com/BetterCWBWStats">
    <img alt="Twitter: JKamue_dev" src="https://img.shields.io/twitter/follow/BetterCWBWStats.svg?style=social" target="_blank" />
  </a>
 <a href="https://twitter.com/JKamue_dev">
    <img alt="Twitter: JKamue_dev" src="https://img.shields.io/twitter/follow/JKamue_dev.svg?style=social" target="_blank" />
  </a>
</p>