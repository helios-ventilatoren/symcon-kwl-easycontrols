### IP-Symcon Modul // Helios easyControls  (by Christoph Bach)
---

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Technische-Details](#2-technische-details)
3. [Systemanforderungen](#2-systemanforderungen)
4. [Installation](#3-installation)
5. [Befehlsreferenz](#4-befehlsreferenz)
6. [Changelog](#5-changelog) 



## 1. Funktionsumfang
Dieses Modul ermöglicht die Kommunikation mit Helios easyControls über IP-Symcon.

**Einstellungsmöglichkeiten in der Modul-Instanz:**
- Geräte IP
- Geräte Passwort
- Aktualisierungsintervall der Gerätedaten
- Anzeigen/Ausblenden von verschiedenen Variablen
- Bild des Gerätes auslesen und als Medienobjekt anzeigen
- Wie viele Tage vor Filterwechsel die Zustandsvariable auf -Warnung- gesetzt werden soll
- Intervall für den Filter-Wechsel
- Optischen Darstellung der Status-/Fehler-/Warnung- und Info-Meldungen
- Vorgegebene Namen der easyControls-Wochenprogramme durch eigenen Text ersetzen
- Logging für verschiedene "Bereiche" de/aktivieren
- Konfiguration der Benachrichtigungsmethode(n)
- Themengebiete für Benachrichtigungen de/aktivieren
- Modul-Einstellungen (Ping-Überprüfung des Gerätes und erweiterte Debug-Ausgaben de/aktivieren)



## 2. Technische-Details
Verwendete GUID:
- {859431EC-ED2E-457A-A528-FD6E9C927D66} (Modul)
- {889DFBC4-09A6-4D77-9928-738E5D494362} (Geräte-Instanz)



## 3. Systemanforderungen
- IP-Symcon ab Version 4.3



## 4. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:<br>
`https://gitlab.com/xxxxxxxxxxxxxx/Helios.git`



## 5. Befehlsreferenz
```php
  HELIOS_Update_Data();
```  
Liest alle Informationen (inkl. System-Informationen) zum Gerät von easyControls aus, gibt die Daten als Array zurück schreibt die Daten in die jeweiligen Variablen.

```php
  HELIOS_Update_System_Data();
```  
Liest die System-Informationen zum Gerät von easyControls aus, gibt die Daten als Array zurück schreibt die Daten in die jeweiligen Variablen.



## 6. Changelog
Version 0.9:
  - Erster Test-Release