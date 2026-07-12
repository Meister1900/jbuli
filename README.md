# Fußballmodule für Joomla 6
Responsive Fußball-Ergebnisse, Spielpläne und Tabellen für Joomla 6. Die Spieldaten stammen aus der [OpenLigaDB-API](https://api.openligadb.de/).

## Aktuelle Versionen

- Ergebnismodul `2.1.12`
- Spielplanmodul `2.1.20`
- Tabellenmodul `2.1.9`

## Voraussetzungen und Installation

- Joomla 6 und PHP 8 oder neuer
- PHP-cURL oder aktiviertes `allow_url_fopen`
- Schreibbares Joomla-Cacheverzeichnis
- Ausgehender HTTPS-Zugriff auf `api.openligadb.de`

Die eigene Joomla-Seite darf über HTTP laufen. HTTPS wird nur serverseitig für OpenLigaDB verwendet. Die ZIP-Datei des Moduls wird über **System → Installieren → Erweiterungen** installiert und kann dort auch als Update eingespielt werden.

Die automatische Joomla-Aktualisierung lädt sowohl die Update-Metadaten als auch die per SHA-256 abgesicherten ZIP-Pakete direkt aus diesem GitHub-Repository. Die Prüfsumme jedes Pakets steht in der zugehörigen Datei im Verzeichnis `updater`.


## BUNDESLIGA SPIELPLAN-MODUL FÜR JOOMLA 6
Joomla Modul zur Anzeige aller Spiele eines Vereins aus der 1. oder 2. Fussball Bundesliga

Features

- Stellt alle Bundesliga, DFB Pokal und Champions League Partien eines Vereins dar
- Live-Spiele werden farblich hervorgehoben (Aktualisierungsintervall kann im Backend konfiguriert werden)
- Die Torschützen werden in einem Tooltip angezeigt, wenn man mit der Maus über die Ergebnisse fährt
- Der standardmäßig angezeigte Verein kann im Backend eingestellt werden
- Auswahl eines anderen Vereins der gleichen Liga per Dropdown Feld möglich
- Die Daten des gewählten Vereins werden im Hintergrund per AJAX von OpenLigaDB geladen
- Eigene, Reload-sichere Cachedateien je Joomla-Modulinstanz
- Vereinsnamen können in der Datenbanktabelle angepasst werden
- Breite und Höhe des Moduls können im Backend konfiguriert werden
- Mit der aktuellen Joomla Version 6 und PHP 8 kompatibel
- Garantiert werbe- und kostenfrei!



## FUSSBALL ERGEBNIS-MODUL FÜR JOOMLA 
Joomla Modul zur Anzeige einzelner Spieltage der 1. und 2. Bundesliga, Champions League, Premier League, LaLiga und Serie A

Features

- Stellt die Partien und Ergebnisse vom Webservice OpenLigaDB dar - Keine manuelle Pflege der Daten erforderlich!
- Live-Spiele können farblich hervorgehoben werden (siehe Screenshots)
- NEU: Die Torschützen werden in einem Tooltip angezeigt, wenn man mit der Maus über die Ergebnisse fährt
- Drei Namenslängen: lang, mittel und kurz; die Kurzansicht gruppiert Partien kompakt
- Der eigene Verein kann per CSS hervorgehoben werden
- Der Lieblingsverein wird im Backend dynamisch passend zu Liga und Saison angeboten
- Liga und Saison können im Backend eingestellt werden
- Laufende Saisons zeigen den aktuellen Spieltag; abgeschlossene Bundesliga-Saisons starten beim 34. Spieltag
- Die Daten des gewählten Spieltags werden im Hintergrund per AJAX nachgeladen
- Eigene Cachedateien je Joomla-Modulinstanz
- Vereinsnamen und Kürzel können in der Datenbanktabelle angepasst werden
- Mit der aktuellen Joomla Version 6 und PHP 8 kompatibel
- Garantiert werbe- und kostenfrei!

Aktuelle OpenLigaDB-Kürzel: `bl1`, `bl2`, `la1`, `epl`, `sa` und `ucl`. Internationale Saisons sind nur verfügbar, wenn sie in OpenLigaDB gepflegt werden. Für `ucl/2026` waren Liga und 16 Runden beim Release bereits angelegt, Spiele und Teams jedoch noch nicht veröffentlicht. Fehlende lokale Logos verwenden automatisch die Icon-URL aus der API.



## BUNDESLIGA TABELLEN-MODUL FÜR JOOMLA 6
Joomla Modul zur Anzeige der aktuellen Tabelle der 1. und 2. Fussball Bundesliga

Features

- Berechnet automatisch die aktuelle Tabelle anhand der Spieldaten des OpenLigaDB Webservice
- Gute Performance, da die Tabellendaten per AJAX aus einer lokalen Joomla Tabelle geladen werden. Den Intervall, wie häufig die Tabelle aktualisiert werden soll, kann im Backend konfiguriert werden.
- Vereine die ein Live-Spiel haben können farblich hervorgehoben werden
- Liga und Saison können im Backend eingestellt werden
- Der eigene Verein kann per CSS hervorgehoben werden
- Die Vereinsauswahl im Backend wird passend zu Liga und Saison erzeugt
- Jede Modulinstanz besitzt einen eigenen Cache
- Mit der aktuellen Joomla Version 6 und PHP 8 kompatibel
- Garantiert werbefrei!

## Lizenz

GNU General Public License; siehe [LICENSE](LICENSE).
