# Changelog

## 1.0.14 – 15.07.2026

- Defekte Champions-League-Logo-URLs von OpenLigaDB durch lokale Projekt-Fallbacks für Arsenal, Atlético, Real Madrid, Paris Saint-Germain und Barcelona ergänzt.

## 1.0.13 – 15.07.2026

- LaLiga, Premier League und Serie A aus der Auswahl entfernt.
- Champions-League-Teamlogos zusätzlich aus den Saisonspielen ergänzt.

## 1.0.12 – 15.07.2026

- Eigenständigen Backend-Feldtyp für den sofort sichtbaren Rechtehinweis ergänzt.

## 1.0.11 – 15.07.2026

- Platzhalterhinweis präzisiert und im Backend einen Hinweis auf die notwendigen Bildnutzungsrechte ergänzt.

## 1.0.10 – 15.07.2026

- Ersten Initialen-Platzhalter mit dezentem grauen Verlauf wiederhergestellt und bei fehlendem Porträt einen kurzen, tastaturbedienbaren Backend-Upload-Hinweis ergänzt.

## 1.0.9 – 15.07.2026

- Initialen-Platzhalter wieder als zurückhaltenden, klassischen Kreis ohne Verlauf, Glanz und Innenring dargestellt.

## 1.0.8 – 15.07.2026

- Eigenen Administrator-Helper beim Installieren ergänzt, damit der geschützte Bild-Upload zuverlässig mit der Backend-Session und dem CSRF-Token ausgeführt wird.

## 1.0.7 – 15.07.2026

- Spielerbild-Upload über den Site-AJAX-Endpunkt geführt, damit Joomla nicht fälschlich unter `administrator/modules/` nach dem Site-Modul sucht.
- Uploadberechtigung bleibt an die Joomla-Berechtigung `core.manage` für `com_modules` und den CSRF-Token gebunden.

## 1.0.6 – 15.07.2026

- Eigenständigen Backend-Feldtyp ergänzt, damit ein zuvor im PHP-Opcode-Cache liegender Feldcode den Upload nicht mehr blockieren kann.

## 1.0.5 – 15.07.2026

- Backend-Formfeld lädt den Helper jetzt über den installierten Joomla-Modulpfad.
- Neue eigene Spielerbilder liegen getrennt unter `images/player-uploads/`; frühere, nicht mehr mitgelieferte Porträts werden dadurch nicht mehr im Frontend angezeigt.

## 1.0.4 – 15.07.2026

- Fehlerhafte Pflichtfeldprüfung der Medienauswahl durch einen direkten, berechtigungsgeschützten Backend-Upload ersetzt.
- Spieler werden als nach Namen sortierte OpenLigaDB-Auswahlliste für die gespeicherte Liga und Saison angezeigt; das Modul benennt Uploads automatisch nach der Spieler-ID.
- Initialen-Platzhalter modernisiert; nur echte Spielerporträts vergrößern sich bei Hover oder Tastaturfokus.

## 1.0.3 – 15.07.2026

- Spielerporträts aus dem Installationspaket entfernt.
- Backend-Zuordnung für selbst hochgeladene, lokale Spielerporträts über die Joomla-Medienverwaltung ergänzt.
- Porträts werden nur bei passender OpenLigaDB-Spieler-ID, zulässigem Bildformat und mindestens 150 Pixeln Breite angezeigt.

## 1.0.2 – 13.07.2026

- JED-konforme GPL-Kopfzeilen, Paketlizenz und Bildrechtehinweise ergänzt.

## 1.0.1 – 13.07.2026

- Offizielle Spielerporträts der Top 10 aus Bundesliga und 2. Bundesliga 2025/26 ergänzt.
- 17 Porträts stammen aus den offiziellen Bundesliga-/DFL-Spielerprofilen; drei Fallbackbilder aus Kicker-Spielerprofilen ersetzen derzeit defekte DFL-Assets.
- Bildquellen und Rechtehinweis stehen unter `images/players/SOURCES.md`.

## 1.0.0 – 13.07.2026

- Neues Joomla-6-Modul für OpenLigaDB-Torschützenlisten.
- Liga, Saison und Anzahl der Top-Torschützen im Backend konfigurierbar.
- Vereinszuordnung und Vereinslogos werden aus den Saisondaten ergänzt.
- Optionale lokale Spielerbilder anhand der OpenLigaDB-Spieler-ID mit Vergrößerung bei Hover und Tastaturfokus.
- Instanzbezogener, ausfallsicherer Cache und cachefreie AJAX-Antworten.
