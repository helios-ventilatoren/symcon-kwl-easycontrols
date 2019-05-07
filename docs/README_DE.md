### IP-Symcon Modul // Helios easyControls  (by Christoph Bach - www.bayaro.net)
---
[![Version](https://img.shields.io/badge/Symcon_Version->=%204.3-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.0-green.svg)]()
[![Version](https://img.shields.io/badge/Code-PHP-green.svg)]()


![Helios Logo](/imgs/helios_logo.png)


<br><br>
## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Technische-Details](#2-technische-details)
3. [Systemanforderungen](#2-systemanforderungen)
4. [Installation](#3-installation)
5. [Befehlsreferenz](#4-befehlsreferenz)
6. [Changelog](#5-changelog) 


<br><br>
## 1. Funktionsumfang
Dieses Modul ermöglicht das Auslesen und Steuern von [Helios](https://www.heliosventilatoren.de/de/) Lüftungsanlagen mit [easyControls](https://www.heliosventilatoren.de/de/aktuelles/neues-bei-helios-ventilatoren/81-easycontrols-die-revolutionaere-steuerung-fuer-helios-lueftungsgeraete-mit-waermerueckgewinnung-4) in [IP-Symcon](https://www.symcon.de).

**Einstellungsmöglichkeiten in der Modul-Instanz:**
- Geräte IP
- Geräte Passwort
- Aktualisierungsintervall der Gerätedaten
- De/aktivieren von verschiedenen Variablengruppen
- Bild des Gerätes auslesen und als Medienobjekt anzeigen
- Wie viele Tage vor Filterwechsel die Zustandsvariable auf -Warnung- gesetzt werden soll
- Intervall für den Filter-Wechsel
- Optischen Darstellung der Status-/Fehler-/Warnung- und Info-Meldungen
- Vorgegebene Namen der easyControls-Wochenprogramme durch eigenen Text ersetzen
- Logging für verschiedene Variablen-Gruppen des Moduls de/aktivieren
- Konfiguration der Benachrichtigungsmethode(n)
- Themengebiete für Benachrichtigungen de/aktivieren
- Modul-Einstellungen (Ping-Überprüfung des Gerätes und erweiterte Debug-Ausgaben de/aktivieren)


<br><br>
## 2. Technische-Details
####Verwendete GUID:
| Modul              | Prefix  | GUID                                    |
| :----------------- | :------ | :-------------------------------------- |
| Modul              | *       | {859431EC-ED2E-457A-A528-FD6E9C927D66}  |
| Geräte-Instanz     | HELIOS  | {889DFBC4-09A6-4D77-9928-738E5D494362}  |


<br><br>
## 3. Systemanforderungen
- IP-Symcon ab Version 4.3
- Helios Lüftungsanlage mit easyControls


<br><br>
## 4. Installation
**IPS Version ab 5.1 - Installation über den Module-Store**
- Das Modul kann ganz einfach über den Module-Store von IP-Symcon installiert  werden - einfach im Store nach "Helios" suchen.<br>

**IPS Version kleiner 5.1 - Installation über Module-Control**
- Bei IP-Symcon Versionen kleiner 5.1 steht der Module-Store noch nicht zur Verfügung - hier muss manuell über die Kern Instanz "Module Control" die folgende URL hinzugefügt werden:<br>
`https://www.github.com/Helios/IPS/Helios.git`

...danach kann im Objektbaum von IP-Symcon eine neue Instanz "Helios" hinzugefügt werden. Dann muss nur noch die IP der Lüftungsanlage sowie das easyControls Passwort eintragen werden. Alle weiteren Einstellungen sind optional, um das Modul und die Variablen auf eure Bedürfnisse/Wünsche anzupassen.


<br><br>
## 5. Bedienungsinformationen
##### Hinweise zu Voreinstellungs-Variablen der Kurzprogramme:<br>
- Bevor ein Kurzprogramm über WebFront/App aktiviert werden kann, müssen die für das Kurzprogramm gewünschten Einstellungen in den Voreinstellungs-Variablen vornehmen (über WebFront/App möglich)
- Danach kann das gewünschte Kurzprogramm aktiviert werden (per WebFront/App oder über eine Modul-Funktion in einem eigenen Skript)


<br><br>
## 6. Befehlsreferenz

**Hinweise zu den Modul-Funktionen:**
- Bei allen Funktionen werden evtl. zugehörige Variablen automatisch mit aktualisiert, sofern diese vorhanden und in der Modul-Instanz aktiviert sind.
- Dynamische Daten (Temperaturen, Drehzahlen, ...) werden live vom Gerät abgefragt.
- Mehr oder weniger statische Daten (Firmware-Version, MAC-Adresse, ...) werden beim Übernehmen von Änderungen in der Modul-Instanz, sowie jede Nacht, automatisch intern im Modul aktualisiert und wenn diese Daten über eine der folgenden Funktionen abgefragt werden, wird aus einem internen Modul-Speicher geantwortet.
<br><br><br>

```php
    HELIOS_Afterheater_ActualPower_Get(int $InstanceID);
```
Liest die aktuelle Leistungsstufe der Nachheizung aus und gibt diese als Integer von 0-100% zurück.<br><br>

```php
    HELIOS_Afterheater_HeatOutputDelivered_Get(int $InstanceID);
```
Berechnet die abgegebene Heizleistung der Nachheizung in % und gibt dies als Float-Wert im Bereich von 0.0% bis 100.0% zurück.<br><br>

```php
    HELIOS_Afterheater_Status_Get(int $InstanceID);
```
Liest den Status der Nachheizung aus und gibt diesen als Bool-Wert zurück.<br><br>

```php
    HELIOS_AllData_Get(int $InstanceID, bool $live);
```
Diese Funktion ermöglicht einem die Abfrage aller Daten über einen Aufruf. Die Antwort besteht aus einem großen Array mit allen verfügbaren Informationen.
Wird beim Parameter $live FALSE übergeben, dann werden die Daten aus dem internen Zwischenspeicher des Moduls beantwortet (dieser Zwischenspeicher wird bei einer Live-Anfrage aktualisiert und automatisch jede Nacht) - wird TRUE angegeben, dann werden alle Daten live vom verbundenen Gerät abgefragt (dies kann je nach Anlage mehrere Sekunden dauern).<br><br>

```php
    HELIOS_AllData_Combined_Get(int $InstanceID);
```
Diese Funktion ist ähnlich der Funktion "HELIOS_AllData_Get", allerdings wird bei dieser Funktion das zurückgegebene Array noch etwas "aufgebessert", damit es besser lesbar/verwendbar ist.<br><br>

```php
    HELIOS_Bypass_Get(int $InstanceID);
```
Liest den Status der Bypass Klappe aus. Ist der Bypass geöffnet wird TRUE zurückgegeben, ansonsten FALSE.<br><br>

```php
    HELIOS_CO2Control_Get(int $InstanceID);
```
Liest die aktuelle Einstellung der CO2-Steuerung aus und gibt einen Integer-Wert zurück.<br>
0 = Aus<br>
1 = Stufig<br>
2 = Stufenlos<br><br>

```php
    HELIOS_CO2Control_Set(int $InstanceID, int $value);
```
Funktion zur Einstellung der CO2-Steuerung.<br>
Gültige Integer-Werte für $value sind:<br>
0 (Aus)<br>
1 (Stufig)<br>
2 (Stufenlos)<br><br>

```php
    HELIOS_CO2Sensor_Get(int $InstanceID, int $number);
```
Aktuellen Wert eines CO2-Sensor abfragen. Bei $number muss hier die Nummer vom gewünschten Sensor angegeben werden (1 bis max. 8 - kann je nach Anlage variieren).
Zurückgegeben wird ein Array mit "Description" (Beschreibung des Sensores aus easyControls UI) und "CO2_ppm" (aktueller ppm Wert des Sensors).<br><br>

```php
    HELIOS_CO2Sensors_All_Get(int $InstanceID);
```
Abfrage aller CO2-Sensoren über eine Funktion. Zurückgegeben wird ein Array mit den Sensornummern 1 bis 8 und (sofern der jeweilige Sensor verbaut ist) die Beschreibung/Text und der aktuelle Wert.<br><br>

```php
    HELIOS_Defrost_State_Get(int $InstanceID);
```
Diese Funktion liest den aktuellen Status der Frostschutz-Funktion aus und gibt ein Array mit Baugruppen und einem zugehörigen Bool-Wert zurück. Wenn der Frostschutz aktuell aktiv ist, dann TRUE, ansonsten FALSE.<br><br>

```php
    HELIOS_DeviceImage_Get(int $InstanceID);
```
Gibt ein Bild der verbundenen Anlage aus (in Base64-Kodierung).<br><br>

```php
    HELIOS_FanLevel_Get(int $InstanceID);
```
Liest die aktuelle Lüfterstufe aus der verbundenen Anlage aus und gibt diese als Integer-Wert zurück. Diese Funktion funktioniert bei allen Betriebsarten, nicht nur bei manuell eingesteller Lüfterstufe.<br><br>

```php
    HELIOS_FanLevel_Percent_Get(int $InstanceID);
```
Liest die aktuelle Lüfterstufe in % aus der verbundenen Anlage aus und gibt diese als Integer-Wert (0 bis 100%) zurück. Diese Funktion funktioniert bei allen Betriebsarten, nicht nur bei manuell eingesteller Lüfterstufe.<br><br>

```php
    HELIOS_FanLevel_Set(int $InstanceID, int $fanlevel);
```
Setzen der aktuellen Lüferstufe auf den bei $fanlevel angegebenen Wert (0 bis 4). Die verbundene Anlage wird automatisch auf manuelle Betriebsart und die angegebene Lüfterstufe eingestellt.<br>
Bei dieser Funktion wird die minimale Lüferstufe (Einstellung in easyControls) beachtet. Darf als minimale Lüferstufe nur die Stufe 1 gewählt werden, dann kann auch über diese Funktion minimal nur die Stufe 1 gesetzt werden.<br><br>

```php
    HELIOS_FanLevel_SetForPeriod(int $InstanceID, int $fanlevel, int $minutes);
```
Setzen der aktuellen Lüferstufe auf den bei $fanlevel angegebenen Wert (0 bis 4) für die bei $minutes angegebene Zeit (in Minuten). Die verbundene Anlage wird automatisch auf manuelle Betriebsart und die angegebene Lüfterstufe eingestellt.<br>
Nach der angegebenen Zeit wird die verbundene Anlage wieder auf die Betriebsart "Automatisch" gestellt.<br>
Bei dieser Funktion wird die minimale Lüferstufe (Einstellung in easyControls) beachtet. Darf als minimale Lüferstufe nur die Stufe 1 gewählt werden, dann kann auch über diese Funktion minimal nur die Stufe 1 gesetzt werden.<br><br>

```php
    HELIOS_FanSpeed_ExhaustAir_Get(int $InstanceID);
```
Auslesen der aktuellen Lüftergeschwindigkeit vom Lüfter "Abluft". Zurückgegeben wird die aktuelle Drehzahl als Integer-Wert in "rpm".<br><br>

```php
    HELIOS_FanSpeed_SupplyAir_Get(int $InstanceID);
```
Auslesen der aktuellen Lüftergeschwindigkeit vom Lüfter "Zuluft". Zurückgegeben wird die aktuelle Drehzahl als Integer-Wert in "rpm".<br><br>

```php
    HELIOS_Filter_ChangeInterval_Get(int $InstanceID);
```
Auslesen des in easyControls eingestellten Filter-Wechsel-Intervall in Monaten (Integer-Wert).<br><br>

```php
    HELIOS_Filter_ChangeInterval_Set(int $InstanceID, int $value);
```
Setzen des Filter-Wechsel-Intervall. Die Angabe bei $value muss in Monaten erfolgen. Der gültige Bereich geht von 3 bis 12 Monaten.<br><br>

```php
    HELIOS_Filter_RemainingDays_Get(int $InstanceID);
```
Auslesen der Restlaufzeit bis zum Wechsel des Filters in Tagen (Integer-Wert).<br><br>

```php
    HELIOS_Filter_Reset(int $InstanceID);
```
Zurücksetzen der Restlaufzeit des Filters auf den eingestellten Filter-Wechsel-Intervall (beim Wechsel des Filters).<br><br>

```php
    HELIOS_HeatRecoveryEfficiency_Calculate(int $InstanceID);
```
Berechnet die aktuelle Rückwärmezahl (Richtlinie VDI 2071 "Wärmerückgewinnung in Raumlufttechnischen Anlagen").<br><br>

```php
    HELIOS_HumidityControl_Get(int $InstanceID);
```
Liest die aktuelle Einstellung der Feuchte-Steuerung aus und gibt einen Integer-Wert zurück.<br>
0 = Aus<br>
1 = Stufig<br>
2 = Stufenlos<br><br>

```php
    HELIOS_HumidityControl_Set(int $InstanceID, int $value);
```
Funktion zur Einstellung der Feuchte-Steuerung.<br>
Gültige Integer-Werte für $value sind:<br>
0 (Aus)<br>
1 (Stufig)<br>
2 (Stufenlos)<br><br>

```php
    HELIOS_HumidityControl_Internal_Get(int $InstanceID);
```
Liest die aktuelle Einstellung der internen Feuchte-Steuerung aus (sofern in der Anlage vorhanden) und gibt einen Integer-Wert zurück.<br>
0 = Aus<br>
1 = Stufenlos<br><br>

```php
    HELIOS_HumidityControl_Internal_ExhaustAir_Get(int $InstanceID);
```
???<br><br>  


```php
    HELIOS_HumidityControl_Internal_Set(int $InstanceID, int $value);
```
Funktion zur Einstellung der internen Feuchte-Steuerung (sofern in der Anlage vorhanden).<br>
Gültige Integer-Werte für $value sind:<br>
0 (Aus)<br>
1 (Stufenlos)<br><br>

```php
    HELIOS_HumiditySensor_Get(int $InstanceID, int $number);
```
Aktuellen Wert eines Feuchte-Sensor abfragen. Bei $number muss hier die Nummer vom gewünschten Sensor angegeben werden (1 bis max. 8 - kann je nach Anlage variieren).
Zurückgegeben wird ein Array mit "Description" (Beschreibung des Sensores aus easyControls UI), "RelativeHumidity" (Relative Feuchtigkeit) und "TemperatureCelsius" (aktuelle Temperatur in Grad Celsius).<br><br>

```php
    HELIOS_HumiditySensors_All_Get(int $InstanceID);
```
Abfrage aller Feuchte-Sensoren über eine Funktion. Zurückgegeben wird ein Array mit den Sensornummern 1 bis 8 und (sofern der jeweilige Sensor verbaut ist) die Beschreibung/Text, die aktuelle relative Feuchtigkeit und die aktuelle Temperatur in Grad Celsius.<br><br>

```php
    HELIOS_OperatingMode_Get(int $InstanceID);
```
Abfragen der aktuellen Betriebsart. Zurückgegeben wird ein Integer-Wert mit folgenden möglichen Werten:<br>
0 = Automatisch<br>
1 = Manuell<br>
2 = Partybetrieb<br>
3 = Ruhebetrieb<br>
4 = Urlaubsbetrieb<br><br>

```php
    HELIOS_OperatingMode_Party_Set(int $InstanceID, bool $activate, int $fanlevel, int $duration);
```
Funktion zum De/aktivieren des Kurzprogramm "Partybetrieb".<br>
Wird beim Parameter $active FALSE übergeben, dann wird der Partybetrieb beendet und die Anlage auf die Betriebsart "Automatisch" gestellt.<br>
Setzt man den Parameter $activate auf TRUE, dann müssen die folgenden Parameter mit angegeben werden:<br>
- fanlevel = 0 bis 4 (unter Beachtung der minimal erlaubten Lüfterstufe)
- duration = 5 bis 180 Minuten<br><br>

```php
    HELIOS_OperatingMode_Party_SetWithPresets(int $InstanceID);
```
Aktivieren des Kurzprogramm "Partybetrieb" unter Verwendung der in den Modul-Variablen gewählten Voreinstellungen (siehe weiter oben unter Punkt 5 [Bedienungsinformationen] beim Absatz "Hinweise zu Voreinstellungs-Variablen der Betriebsarten/Kurzprogramme").<br><br>

```php
    HELIOS_OperatingMode_RemainingMinutes_Get(int $InstanceID);
```
Auslesen der Restlaufzeit des aktuellen Kurzprogrammes. Zurückgegeben wird ein Integer-Wert in Minuten.<br><br>

```php
    HELIOS_OperatingMode_Set(int $InstanceID, string $value);
```
Setzen der aktuellen Betriebsart der verbundenen Anlage. Gültige Werte für $value sind "auto" oder "manu".<br><br>

```php
    HELIOS_OperatingMode_Vacation_Set(int $InstanceID, bool $activate, int $program, int $fanlevel, string $dateStart, string $dateEnd, int $intervalTime, int $activationPeriod);
```
Funktion zum De/aktivieren des Kurzprogramm "Urlaubsbetrieb".<br>
Wird beim Parameter $active FALSE übergeben, dann wird der Urlaubsbetrieb beendet und die Anlage auf die Betriebsart "Automatisch" gestellt.<br>
Setzt man den Parameter $activate auf TRUE, dann müssen die folgenden Parameter mit angegeben werden:<br>
- program = 0 Aus, 1 Intervall, 2 Konstant
- fanlevel = 0 bis 4 (unter Beachtung der minimal erlaubten Lüfterstufe)
- dateStart = DD.MM.YYYY oder MM.DD.YYYY oder YYYY.MM.DD
- dateEnd = DD.MM.YYYY oder MM.DD.YYYY oder YYYY.MM.DD
- intervalTime = 1 bis 24 Stunden (nur bei "program 1" notwendig/verfügbar)
- activationPeriod = 5 bis 300 Minuten (nur bei "program 1" notwendig/verfügbar)<br><br>

```php
    HELIOS_OperatingMode_Vacation_SetWithPresets(int $InstanceID);
```
Aktivieren des Kurzprogramm "Urlaubsbetrieb" unter Verwendung der in den Modul-Variablen gewählten Voreinstellungen (siehe weiter oben unter Punkt 5 [Bedienungsinformationen] beim Absatz "Hinweise zu Voreinstellungs-Variablen der Kurzprogramme").<br><br>

```php
    HELIOS_OperatingMode_Whisper_Set(int $InstanceID, bool $activate, int $fanlevel, int $duration);
```
Funktion zum De/aktivieren des Kurzprogramm "Ruhebetrieb".<br>
Wird beim Parameter $active FALSE übergeben, dann wird der Partybetrieb beendet und die Anlage auf die Betriebsart "Automatisch" gestellt.<br>
Setzt man den Parameter $activate auf TRUE, dann müssen die folgenden Parameter mit angegeben werden:<br>
- fanlevel = 0 bis 4 (unter Beachtung der minimal erlaubten Lüfterstufe)
- duration = 5 bis 180 Minuten<br><br>

```php
    HELIOS_OperatingMode_Whisper_SetWithPresets(int $InstanceID);
```
Aktivieren des Kurzprogramm "Ruhebetrieb" unter Verwendung der in den Modul-Variablen gewählten Voreinstellungen (siehe weiter oben unter Punkt 5 [Bedienungsinformationen] beim Absatz "Hinweise zu Voreinstellungs-Variablen der Kurzprogramme").<br><br>

```php
    HELIOS_Preheater_ActualPower_Get(int $InstanceID);
```
Liest die aktuelle Leistungsstufe der Vorheizung aus und gibt diese als Integer von 0-100% zurück.<br><br>

```php
    HELIOS_Preheater_HeatOutputDelivered_Get(int $InstanceID);
```
Berechnet die abgegebene Heizleistung der Vorheizung in % und gibt dies als Float-Wert im Bereich von 0.0% bis 100.0% zurück.<br><br>

```php
    HELIOS_Preheater_Status_Get(int $InstanceID);
```
Liest den Status der Vorheizung aus und gibt diesen als Bool-Wert zurück.<br><br>

```php
    HELIOS_System_AUTOatMidnight_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "AUTO um Mitternacht" aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_AUTOatMidnight_Set(int $InstanceID, bool $value);
```
Funktion zum De/aktivieren der Einstellung "AUTO um Mitternacht".<br>
TRUE = Anlage schaltet um Mitternacht in die Betriebsart "Automatik"<br>
FALSE = Anlage schaltet um Mitternacht NICHT in die Betriebsart "Automatik"<br><br>

```php
    HELIOS_System_CloudSync_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "Cloud Synchronisierung" aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_CloudSync_Set(int $InstanceID, bool $value);
```
Funktion zum De/aktivieren der Einstellung "Cloud Synchronisierung".<br>
TRUE = Cloud Synchronisierung ist aktiv<br>
FALSE = Cloud Synchronisierung ist inaktiv<br><br>

```php
    HELIOS_System_Date_Get(int $InstanceID);
```
Liest das aktuelle Datum vom verbundenen Gerät aus easyControls aus (z.B. "26.04.2019").<br><br>

```php
    HELIOS_System_DateFormat_Get(int $InstanceID);
```
Liest das in easyControls eingestellte Datumsformat aus (z.B. "dd.mm.yyyy").<br><br>

```php
    HELIOS_System_DaylightSavingTimeMode_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "Sommerzeit-Modus" aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_DaylightSavingTimeMode_Set(int $InstanceID, bool $value);
```
Funktion zum De/aktivieren der Einstellung "Sommerzeit-Modus".<br>
TRUE = Sommerzeit-Modus ist aktiv<br>
FALSE = Sommerzeit-Modus ist inaktiv<br><br>

```php
    HELIOS_System_FanLevelMin_Get(int $InstanceID);
```
Funktion zum Auslesen der minimal erlaubten Lüfterstufe. Die Antwort ist ein Integer-Wert und kann 0 oder 1 sein.<br><br>

```php
    HELIOS_System_Language_Get(int $InstanceID);
```
Auslesen der in easyControls eingestellten Sprache. Zurückgeben wird das Länderkürzel als String  (z.B. "de").<br><br>

```php
    HELIOS_System_MACAddress_Get(int $InstanceID);
```
Liest die MAC-Adresse der verbundenen Anlage aus. Zurückgegeben wird ein String im Format "xx:xx:xx:xx:xx:xx".<br><br>

```php
    HELIOS_System_Messages_Error_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Fehler" aus der verbundenen Anlage aus und gibt ein Array zurück.<br>
Im Falle eines Fehlers steht im zugehörigen Array-Eintrag nicht "", sondern die entsprechende Fehlermeldung.<br><br>

```php
    HELIOS_System_Messages_ErrorCount_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Fehler" aus der verbundenen Anlage aus und gibt die Anzahl der Meldungen als Integer zurück.<br><br>

```php
    HELIOS_System_Messages_Info_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Information" aus der verbundenen Anlage aus und gibt ein Array zurück.<br>
Im Falle einer vorhandenen Info-Meldung steht im zugehörigen Array-Eintrag nicht "", sondern die entsprechende Information.<br><br>

```php
    HELIOS_System_Messages_InfoCount_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Information" aus der verbundenen Anlage aus und gibt die Anzahl der Meldungen als Integer zurück.<br><br>

```php
    HELIOS_System_Messages_Warning_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Warnung" aus der verbundenen Anlage aus und gibt ein Array zurück.<br>
Im Falle einer Warnung steht im zugehörigen Array-Eintrag nicht "", sondern die entsprechende Warnung.<br><br>

```php
    HELIOS_System_Messages_WarningCount_Get(int $InstanceID);
```
Liest alle Systemmeldungen vom Typ "Fehler" aus der verbundenen Anlage aus und gibt die Anzahl der Meldungen als Integer zurück.<br><br>

```php
    HELIOS_System_Messages_Status_Get(int $InstanceID);
```
Liest alle Statusmeldungen aus der verbundenen Anlage aus und gibt ein Array zurück.<br>
Jeder aktive Status steht im zugehörigen Array-Eintrag. Ist ein bestimmter Status nicht aktiv, steht "" im Array-Eintrag.<br><br>

```php
    HELIOS_System_Messages_Reset(int $InstanceID);
```
Funktion zum Zurücksetzen aller Systemmeldungen (Fehler, Warnungen, ...).<br><br>

```php
    HELIOS_System_Modbus_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "Modbus" aus der verbundenen Anlage aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_Modbus_Set(int $InstanceID, bool $value);
```
Funktion zum De/aktivieren der Einstellung "Modbus".<br>
TRUE = Modbus ist aktiv<br>
FALSE = Modbus ist inaktiv<br><br>

```php
    HELIOS_System_OperatingHours_Afterheater_Get(int $InstanceID);
```
Auslesen der Betriebsstunden (als FLOAT-Wert) der Nachheizung.<br><br>

```php
    HELIOS_System_OperatingHours_ExhaustAirFan_Get(int $InstanceID);
```
Auslesen der Betriebsstunden (als FLOAT-Wert) des Abluftventilator.<br><br>

```php
    HELIOS_System_OperatingHours_Preheater_Get(int $InstanceID);
```
Auslesen der Betriebsstunden (als FLOAT-Wert) der Vorheizung.<br><br>

```php
    HELIOS_System_OperatingHours_SupplyAirFan_Get(int $InstanceID);
```
Auslesen der Betriebsstunden (als FLOAT-Wert) des Zuluftventilator.<br><br>

```php
    HELIOS_System_OrderNumber_Get(int $InstanceID);
```
Auslesen der Bestellnummer der verbundenen Anlage.<br><br>

```php
    HELIOS_System_ProductionCode_Get(int $InstanceID);
```
Auslesen des Sicherheitsnummer der verbundenen Anlage.<br><br>

```php
    HELIOS_System_SecurityNumber_Get(int $InstanceID);
```
Auslesen des Produktionscode der verbundenen Anlage.<br><br>

```php
    HELIOS_System_SensorControlSleepMode_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "Ruhemodus für Fühlerregelung" aus der verbundenen Anlage aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_SensorControlSleepMode_Set(int $InstanceID, bool $activate, string $timeFrom, string $timeTo);
```
Funktion zum De/aktivieren oder Ändern der Einstellung "Ruhemodus für Fühlerregelung".<br>
Die Parameter $timeFrom und $timeTo sind zum Definieren der gewünschten Ruhezeit und müssen im Format "HH:MM" (z.B. "16:00") übermittelt werden. Der Parameter $activate ist zum De/aktivieren der Einstellung.<br>
TRUE = Ruhemodus für Fühlerregelung ist aktiv<br>
FALSE = Ruhemodus für Fühlerregelung ist inaktiv<br><br>

```php
    HELIOS_System_SensorControlSleepModeFROM_Get(int $InstanceID);
```
Liest die aktuell eingestellte "Start-Zeit" des "Ruhemodus für Fühlerregelung" aus und gibt diese als String im Format "HH:MM" (z.B. "19:00) zurück.<br><br>

```php
    HELIOS_System_SensorControlSleepModeFROM_Set(int $InstanceID, string $value);
```
Funktion zum Setzen/Ändern der "Start-Zeit" des "Ruhemodus für Fühlerregelung". Die Zeit muss als String im Format "HH:MM" (z.B. "19:00) übermittelt werden.<br><br>

```php
    HELIOS_System_SensorControlSleepModeTO_Get(int $InstanceID);
```
Liest die aktuell eingestellte "Ende-Zeit" des "Ruhemodus für Fühlerregelung" aus und gibt diese als String im Format "HH:MM" (z.B. "19:00) zurück.<br><br>

```php
    HELIOS_System_SensorControlSleepModeTO_Set(int $InstanceID, string $value);
```
Funktion zum Setzen/Ändern der "Ende-Zeit" des "Ruhemodus für Fühlerregelung". Die Zeit muss als String im Format "HH:MM" (z.B. "19:00) übermittelt werden.<br><br>

```php
    HELIOS_System_SerialNumber_Get(int $InstanceID);
```
Auslesen des Seriennummer der verbundenen Anlage.<br><br>

```php
    HELIOS_System_SoftwareUpdateAutomatic_Get(int $InstanceID);
```
Liest die aktuelle Einstellung für "Automatische Softwareupdates" aus der verbundenen Anlage aus. Die Antwort ist ein Bool-Wert.<br><br>

```php
    HELIOS_System_SoftwareUpdateAutomatic_Set(int $InstanceID, bool $value);
```
Funktion zum De/aktivieren der Einstellung "Automatische Softwareupdates".<br>
TRUE = Automatische Softwareupdates sind aktiv<br>
FALSE = Automatische Softwareupdates sind inaktiv<br><br>

```php
    HELIOS_System_SoftwareVersion_Get(int $InstanceID);
```
Auslesen des Software-Version der verbundenen Anlage. Die Version wird als FLOAT zurückgeben (z.B. 2.27), damit man in eigenen Skripten auch mit ">" oder "<" arbeiten kann.<br><br>

```php
    HELIOS_System_Time_Get(int $InstanceID);
```
Liest die aktuelle Uhrzeit aus der verbundenen Anlage aus. Die Antwort ist ein String im Format "HH:MM:SS" (z.B. "21:10:58").<br><br>

```php
    HELIOS_System_TimezoneGMT_Get(int $InstanceID);
```
Liest die in der verbundenen Anlage eingestellte Zeitzone (GMT) aus. Für Deutschland würde der String "2" zurückgegeben werden.<br><br>

```php
    HELIOS_System_Type_Get(int $InstanceID);
```
Liest den Typ/das Modell verbundenen Anlage aus und gibt diesen als String zurück (z.B. "KWL EC 500W R").<br><br>

```php
    HELIOS_Temperature_Comfort_Get(int $InstanceID);
```
Liest die in der verbundenen Anlage eingestellte Behaglichkeitstemperatur und gibt diese als FLOAT-Wert zurück (z.B. 17.5).<br><br>

```php
    HELIOS_Temperature_Sensor_Get(int $InstanceID, int $number);
```
Aktuellen Wert eines Temperatur-Sensor abfragen. Bei $number muss hier die Nummer vom gewünschten Sensor angegeben werden (1 bis max. 7 - je nach verbauten Sensoren).
Zurückgegeben wird ein Array mit "Description" (Beschreibung des Sensores aus easyControls UI) und "Value_C" (aktuelle Temperatur in Grad Celsius).<br>
Ist der angefragte Sensor nicht vorhanden, wird FALSE zurückgegeben.<br><br>

```php
    HELIOS_Temperature_Sensors_All_Get(int $InstanceID);
```
Abfrage aller Temperatur-Sensoren über eine Funktion. Zurückgegeben wird ein Array mit den Sensornummern 1 bis 7 und (sofern der jeweilige Sensor verbaut ist) die Beschreibung/Text und die aktuelle Temperatur in Grad Celsius.<br><br>

```php
    HELIOS_VOCControl_Get(int $InstanceID);
```
Liest die aktuelle Einstellung der VOC-Steuerung aus und gibt diesen als Integer-Wert zurück.<br>
0 = Aus<br>
1 = Stufig<br>
2 = Stufenlos<br><br>

```php
    HELIOS_VOCControl_Set(int $InstanceID, int $value);
```
Funktion zur Einstellung der VOC-Steuerung.<br>
Gültige Integer-Werte für $value sind:<br>
0 (Aus)<br>
1 (Stufig)<br>
2 (Stufenlos)<br><br>

```php
    HELIOS_VOCSensor_Get(int $InstanceID, int $number);
```
Aktuellen Wert eines VOC-Sensor abfragen. Bei $number muss hier die Nummer vom gewünschten Sensor angegeben werden (1 bis max. 8 - je nach verbauten Sensoren).
Zurückgegeben wird ein Array mit "Description" (Beschreibung des Sensores aus easyControls UI) und "VOC_ppm" (Flüchtige organische Verbindungen in Teile pro Million).<br>
Ist der angefragte Sensor nicht vorhanden, wird FALSE zurückgegeben.<br><br>

```php
    HELIOS_VOCSensors_All_Get(int $InstanceID);
```
Abfrage aller VOC-Sensoren über eine Funktion. Zurückgegeben wird ein Array mit den Sensornummern 1 bis 7 und (sofern der jeweilige Sensor verbaut ist) die Beschreibung/Text und die flüchtigen organischen Verbindungen in Teile pro Million (ppm).<br><br>

```php
    HELIOS_WeekProgram_Get(int $InstanceID);
```
Liest das eingestellte Wochenprogramm aus der verbundenen Anlage als Integer-Wert aus.<br>
0 = Standard 1<br>
1 = Standard 2<br>
2 = Standard 3<br>
3 = Benutzerdefiniert 1<br>
4 = Benutzerdefiniert 2<br>
5 = Aus<br><br>

```php
    HELIOS_WeekProgram_Set(int $InstanceID, int $value);
```
Funktion zum Einstellen des gewünschten Wochenprogrammes. Das Wochenprogramm muss als Integer-Wert angegeben werden.<br>
0 (Standard 1)<br>
1 (Standard 2)<br>
2 (Standard 3)<br>
3 (Benutzerdefiniert 1)<br>
4 (Benutzerdefiniert 2)<br>
5 (Aus)<br><br>

```php
    HELIOS_Update_Data();
```  
Liest alle Informationen (inkl. System-Informationen) zum Gerät von easyControls aus, gibt die Daten als Array zurück schreibt die Daten in die jeweiligen Variablen.

```php
    HELIOS_Update_System_Data();
```  
Liest die System-Informationen zum Gerät von easyControls aus, gibt die Daten als Array zurück schreibt die Daten in die jeweiligen Variablen.



<br><br>
## 7. Changelog
Version 1.0:
- Erster Release