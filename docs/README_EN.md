### IP-Symcon Module // Helios  (Copyright 2019 Christoph Bach - [www.bayaro.net](https://www.bayaro.net))
---
[![Version](https://img.shields.io/badge/Symcon_Version->=%204.3-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Modul_Version-1.0-green.svg)]()
[![Version](https://img.shields.io/badge/Code-PHP-green.svg)]()
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)<br><br>


![Helios Logo](/imgs/helios_logo.png)


## Documentation

**Table of contents**

1. [Features](#1-features) 
2. [Technical details](#2-technical-details)
3. [System requirements](#3-system-requirements)
4. [Installation](#4-installation)
5. [Operation information](#5-operating-information)<br>
5.1. [Notes on technical modifications to the ventilation system](#51-notes-on-technical-modifications-to-the-ventilation-systembr)<br>
5.2. [Notes on preset variables of the short programs](#52-notes-on-preset-variables-of-the-short-programsbr)<br>
5.3. [Notes on the weeky schedule in IP-Symcon and weekly program in easyControls](#53-notes-on-the-weekly-schedule-in-ip-symcon-and-weekly-program-in-easycontrolsbr)
6. [Command reference](#6-command-reference) 
7. [Changelog](#7-changelog)
8. [License](#8-license)


<br><br>
## 1. Features
This module enables the reading and control of [Helios](https://www.heliosventilatoren.de/de/) KWL ventilation systems with [easyControls](https://www.heliosventilatoren.de/de/aktuelles/neues-bei-helios-ventilatoren/81-easycontrols-die-revolutionaere-steuerung-fuer-helios-lueftungsgeraete-mit-waermerueckgewinnung-4) in [IP-Symcon](https://www.symcon.de).<br>
No Internet connection to the Helios Cloud Portal (www.easycontrols.net) is required to read/control the ventilation system via this module. The communication takes place directly with the easyControls control of the ventilation system in the local network, without Helios Cloud Portal.<br><br> 

**Settings in the module instance:**
- Devices IP address
- Devices password
- Update interval of the device data
- De/activate different variable groups
- Read out the image of the device and display it as a media object
- How many days before the filter change the state variable should be set to -Warning-
- Filter change interval
- Optical display of status/error/warning and info messages
- Replace the default names of the easyControls week programs with your own text (in IP-Symcon only)
- De/activate logging for different variable groups of the module
- Configuration of the notification method(s)
- Enable/disable subject areas for notifications
- Module settings (de/activate ping check of device and advanced debug output)


<br><br>
## 2. Technical details
#### GUID used:
| Module             | Prefix  | GUID                                    |
| :----------------- | :------ | :-------------------------------------- |
| Module             | *       | {859431EC-ED2E-457A-A528-FD6E9C927D66}  |
| Device-Instance    | HELIOS  | {889DFBC4-09A6-4D77-9928-738E5D494362}  |


<br><br>
## 3. System requirements
- IP-Symcon Version 4.3 or higher
- Helios KWL ventilation system with easyControls


<br><br>
## 4. Installation
**IPS Version from 5.1 - Installation via the Module Store**
- The module can easily be installed via the module store of IP-Symcon - just search the store for "Helios".<br>

**IPS version less than 5.1 - Installation via Module Control**
- For IP-Symcon versions smaller than 5.1 the Module Store is not available - here the following URL has to be added manually via the core instance "Module Control":<br>
`https://www.github.com/Helios/IPS/Helios.git`

...then a new instance "Helios" can be added to the object tree of IP-Symcon. Then only the IP of the ventilation system and the easyControls password have to be entered. All other settings are optional to adapt the module and the variables to your needs.


<br><br>
## 5. Operating information
##### 5.1 Notes on technical modifications to the ventilation system:<br>
- If technical changes are made to the ventilation system (e.g. new sensors installed or sensors removed), then a change must be made in the module instance in the IP-Symcon (e.g. a check mark must be removed and directly set again) and then "Apply" or "Apply changes" must be pressed. This is necessary so that the IPS module can re-read the currently installed components.
- Alternatively, the complete IP-Symcon can also be restarted, and the currently installed components of the ventilation system are also read out again. 

##### 5.2 Notes on preset variables of the short programs:<br>
- Before a short program can be activated via WebFront/App, the desired settings for the short program must be made in the preset variables (possible via WebFront/App).
- Afterwards the desired short program can be activated (via WebFront/App or via a module function in a separate script)<br><br>

##### 5.3 Notes on the weekly schedule in IP-Symcon and weekly program in easyControls:<br>
- The weekly program in the easyControls web interface and the weekly schedule below this IP-Symcon module work independently of each other and are 2 different control options!
- If the weekly schedule is activated in the IP-Symcon, the weekly program is automatically deactivated in the easyControls!
- If the weekly schedule is deactivated in the IP-Symcon, then the weekly program is not automatically activated in easyControls!
- If the weekly program is activated in easyControls, then the weekly schedule is automatically deactivated in IP-Symcon!
- If the weekly program is deactivated in easyControls, the weekly schedule is not automatically activated in IP-Symcon!


<br><br>
## 6. Command reference

**Notes on the module functions:**
- Any associated variables are automatically updated with all functions, provided they are available and activated in the module instance.
- Dynamic data (temperatures, speeds, ...) are queried live by the device.
- More or less static data (firmware version, MAC address, ...) are automatically updated internally in the module when changes are accepted in the module instance, as well as every night, and when these data are queried via one of the following functions, an internal module memory is used to respond.
<br><br><br>

```php
    HELIOS_Afterheater_ActualPower_Get(int $InstanceID);
```
Reads the current power level of the after heater and returns it as an integer of 0-100%.<br><br>

```php
    HELIOS_Afterheater_HeatOutputDelivered_Get(int $InstanceID);
```
Calculates the delivered heat output of the after heater in % and returns this as a float value in the range from 0.0% to 100.0%.<br><br>

```php
    HELIOS_Afterheater_Status_Get(int $InstanceID);
```
Reads the status of the after heater and returns it as a Boolean value.<br><br>

```php
    HELIOS_AllData_Get(int $InstanceID, bool $live);
```
This function allows you to retrieve all data via one call. The answer consists of a large array with all available information.
If FALSE if passed in the parameter $live, then the data is answered from the internal buffer of the module (this buffer is updated during a live request and automatically every night) - if TRUE is passed, then all data is queried live from the connected device (this can take several seconds depending on the system).<br><br>

```php
    HELIOS_AllData_Combined_Get(int $InstanceID);
```
This function is similar to the function "HELIOS_AllData_Get", but this function "improves" the returned array a bit, so it is easier readable/usable.<br><br>

```php
    HELIOS_Bypass_Get(int $InstanceID);
```
Reads the status of the bypass flap. If the bypass is open, TRUE is returned, otherwise FALSE.<br><br>

```php
    HELIOS_CO2Control_Get(int $InstanceID);
```
Reads the current setting of the CO2 control and returns an integer value.<br>
- 0 = Off<br>
- 1 = Stepped<br>
- 2 = Stepless<br><br>

```php
    HELIOS_CO2Control_Set(int $InstanceID, int $value);
```
Function for setting the CO2 control.<br>
Valid integer values for $value are:<br>
- 0 (Off)<br>
- 1 (Stepped) <br>
- 2 (Steppless)<br><br>

```php
    HELIOS_CO2Sensor_Get(int $InstanceID, int $number);
```
Query the current value of a CO2 sensor. For $number, the number of the desired sensor must be entered here (1 to max. 8 - may vary depending on your system).
An array is returned with "Description" (description of the sensor from easyControls UI) and "CO2_ppm". (current ppm value of the sensor).<br><br>

```php
    HELIOS_CO2Sensors_All_Get(int $InstanceID);
```
Query of all CO2 sensors via one function. An array with the sensor numbers 1 to 8 and (if the respective sensor is installed) the description/text and the current value is returned.<br><br>

```php
    HELIOS_Defrost_State_Get(int $InstanceID);
```
This function reads the current status of the antifrost function and returns an array with components and a corresponding Boolean value. If the frost protection is currently active, then TRUE will be returned, otherwise FALSE.<br><br>

```php
    HELIOS_DeviceImage_Get(int $InstanceID);
```
Outputs a picture of the connected system (in Base64 encoding).<br><br>

```php
    HELIOS_FanLevel_Get(int $InstanceID);
```
Reads the current fan stage from the connected system and returns it as an integer value. This function works with all operating modes, not only with manually set fan speed.<br><br>

```php
    HELIOS_FanLevel_Percent_Get(int $InstanceID);
```
Reads the current fan stage in % from the connected system and returns it as an integer value (0 to 100%). This function works with all operating modes, not only with manually set fan level.<br><br>

```php
    HELIOS_FanLevel_Set(int $InstanceID, int $fanlevel);
```
Set the current fan level to the value specified at $fanlevel (0/1 to 4). The connected system is automatically set to manual mode and the passed fan level.<br>
With this function, the minimum fan stage (setting in easyControls) is respected. If stage 1 may be selected as the minimum fan stage, then only stage 1 can be set via as minimum with this function.<br><br>

```php
    HELIOS_FanLevel_SetForPeriod(int $InstanceID, int $fanlevel, int $minutes);
```
Set the current fan level to the value specified at $fanlevel (0/1 to 4) for the time specified at $minutes (in minutes). The connected system is automatically set to manual mode and the specified fan level.<br>
After the specified time, the connected system switches to the operating mode "Automatic" by itself.<br>
With this function, the minimum fan stage (setting in easyControls) is respected. If stage 1 may be selected as the minimum fan stage, then only stage 1 can be set via as minimum with this function.<br><br>

```php
    HELIOS_FanSpeed_ExhaustAir_Get(int $InstanceID);
```
Reads the current fan speed from the fan "Exhaust air". The current speed is returned as an integer value in "rpm".<br><br>

```php
    HELIOS_FanSpeed_SupplyAir_Get(int $InstanceID);
```
Reads the current fan speed from the fan "Supply air". The current speed is returned as an integer value in "rpm".<br><br>

```php
    HELIOS_Filter_ChangeInterval_Get(int $InstanceID);
```
Reads out the filter change interval in months (integer value) set in easyControls.<br><br>

```php
    HELIOS_Filter_ChangeInterval_Set(int $InstanceID, int $value);
```
Set the filter change interval. The parameter $value must be passed in months. The valid range is from 3 to 12 months.<br><br>

```php
    HELIOS_Filter_RemainingDays_Get(int $InstanceID);
```
Readout of the remaining time (in days - integer value) until the filter change is necessary.<br><br>

```php
    HELIOS_Filter_Reset(int $InstanceID);
```
Resets the remaining time of the filter to the set filter change interval (when changing the filter).<br><br>

```php
    HELIOS_HeatRecoveryEfficiency_Calculate(int $InstanceID);
```
Calculates the current heat recovery efficiency (Guideline VDI 2071 "Heat recovery in heating, ventilation and air conditioning plants").<br><br>

```php
    HELIOS_HumidityControl_Get(int $InstanceID);
```
Reads out the current humidity control setting and returns an integer value.<br>
- 0 = Off<br>
- 1 = Stepped<br>
- 2 = Stepless<br><br>

```php
    HELIOS_HumidityControl_Set(int $InstanceID, int $value);
```
Function for setting the humidity control.<br>
Valid integer values for $value are:<br>
- 0 (Off)<br>
- 1 (Stepped)<br>
- 2 (Stepless)<br><br>

```php
    HELIOS_HumidityControl_Internal_Get(int $InstanceID);
```
Reads the current setting of the internal humidity control (if available in the system) and returns an integer value.<br>
- 0 = Off<br>
- 1 = Stepless<br><br>

```php
    HELIOS_HumidityControl_Internal_ExhaustAir_Get(int $InstanceID);
```
Reads the current relative humidity value in the exhaust air duct and returns this value as an integer (0-100% rH).<br><br>  

```php
    HELIOS_HumidityControl_Internal_Set(int $InstanceID, int $value);
```
Function for setting the internal humidity control (if available in the system).<br>
Valid integer values for $value are:<br>
- 0 (Off)<br>
- 1 (Stepless)<br><br>

```php
    HELIOS_HumiditySensor_Get(int $InstanceID, int $number);
```
Query the current value of a humidity sensor. For $number, the number of the desired sensor must be entered here (1 to max. 8 - can vary depending on the system).
An array is returned with "Description" (description of the sensor from easyControls UI), "RelativeHumidity" and "TemperatureCelsius" (current temperature in degrees Celsius).<br><br>

```php
    HELIOS_HumiditySensors_All_Get(int $InstanceID);
```
Query of all humidity sensors via one function. An array is returned with the sensor numbers 1 to 8 and (if the respective sensor is installed) the description/text, the current relative humidity and the current temperature in degrees Celsius.<br><br>

```php
    HELIOS_OperatingMode_Get(int $InstanceID);
```
Query the current operating mode. An integer value is returned with the following possible values:<br>
- 0 = Automatic<br>
- 1 = Manual<br>
- 2 = Party mode<br>
- 3 = Idle mode<br>
- 4 = Vacation mode<br><br>

```php
    HELIOS_OperatingMode_Party_Set(int $InstanceID, bool $activate, int $fanlevel, int $duration);
```
Function for de/activating the short program "Party mode".<br>
If FALSE is passed for the parameter $active, the party mode is cancelled and the system switches to the "Automatic" operating mode.
If the $activate parameter is set to TRUE, the following parameters must be specified:<br>
- fanlevel = 0/1 to 4 (respecting the minimum allowed fan level)
- duration = 5 to 180 minutes<br><br>

```php
    HELIOS_OperatingMode_Party_SetWithPresets(int $InstanceID);
```
Activate the short program "Party mode" using the presets selected in the module variables (see above under section 5 [Operating information - Notes on preset variables of the short programs](#52-notes-on-preset-variables-of-the-short-programsbr)).<br><br>

```php
    HELIOS_OperatingMode_RemainingMinutes_Get(int $InstanceID);
```
Readout of the remaining time of the current short program. An integer value in minutes is returned.<br><br>

```php
    HELIOS_OperatingMode_Set(int $InstanceID, string $mode);
```
Set the current operating mode of the connected system. Valid values for $mode are "auto" or "manu".<br><br>

```php
    HELIOS_OperatingMode_Vacation_Set(int $InstanceID, bool $activate, int $program, int $fanlevel, string $dateStart, string $dateEnd, int $intervalTime, int $activationPeriod);
```
Function to de/activate the short program "Vacation mode".<br>
If FALSE is passed in the parameter $active, vacation mode is cancelled and the system switches to the "Automatic" operating mode.
If the $activate parameter is set to TRUE, the following parameters must be specified:<br>
- program = 0 Off, 1 Interval, 2 Constant
- fanlevel = 0/1 to 4 (respecting the minimum allowed fan level)
- dateStart = DD.MM.YYYY or MM.DD.YYYY or YYYY.MM.DD (depending on setting in easyControls UI)
- dateEnd = DD.MM.YYYY or MM.DD.YYYY or YYYY.MM.DD (depending on setting in easyControls UI)
- intervalTime = 1 to 24 hours (only necessary/available for "program 1")
- activationPeriod = 5 to 300 minutes (only necessary/available for "program 1")<br><br>

```php
    HELIOS_OperatingMode_Vacation_SetWithPresets(int $InstanceID);
```
Activate the short program "Vacation mode" using the presets selected in the module variables (see above under section 5 [Operating information - Notes on preset variables of the short programs](#52-notes-on-preset-variables-of-the-short-programsbr)).<br><br>

```php
    HELIOS_OperatingMode_Whisper_Set(int $InstanceID, bool $activate, int $fanlevel, int $duration);
```
Function for de/activating the short program "idle mode".<br>
If FALSE is passed for the parameter $active, the party mode is cancelled and the system switches to the "Automatic" operating mode.
If the $activate parameter is set to TRUE, the following parameters must be specified:<br>
- fanlevel = 0 to 4 (respecting the minimum allowed fan level)
- duration = 5 to 180 minutes<br><br>

```php
    HELIOS_OperatingMode_Whisper_SetWithPresets(int $InstanceID);
```
Activate the short program "Idle mode" using the presets selected in the module variables (see above under section 5 [Operating information - Notes on preset variables of the short programs](#52-notes-on-preset-variables-of-the-short-programsbr)).<br><br>

```php
    HELIOS_Preheater_ActualPower_Get(int $InstanceID);
```
Reads the current power level of the preheater and returns it as an integer of 0-100%.<br><br>

```php
    HELIOS_Preheater_HeatOutputDelivered_Get(int $InstanceID);
```
Calculates the heat output delivered of the preheater in % and returns this as a float value in the range from 0.0% to 100.0%.<br><br>

```php
    HELIOS_Preheater_Status_Get(int $InstanceID);
```
Reads the status of the preheater and returns it as a Boolean value.<br><br>

```php
    HELIOS_System_AUTOatMidnight_Get(int $InstanceID);
```
Reads the current setting for "AUTO at midnight". It returns a Boolean value.<br><br>

```php
    HELIOS_System_AUTOatMidnight_Set(int $InstanceID, bool $value);
```
Function for de/activating the setting "AUTO at midnight".<br>
- TRUE = System switches to "Automatic" operating mode at midnight<br>
- FALSE = System does NOT switch to "Automatic" operating mode at midnight.<br><br>

```php
    HELIOS_System_CloudSync_Get(int $InstanceID);
```
Reads the current setting for "Cloud Synchronization". It returns a Boolean value.<br><br>

```php
    HELIOS_System_CloudSync_Set(int $InstanceID, bool $value);
```
Function to de/activate the setting "Cloud Synchronization".<br>
- TRUE = Cloud synchronization is active<br>
- FALSE = Cloud synchronization is inactive<br><br>

```php
    HELIOS_System_Date_Get(int $InstanceID);
```
Reads the current date from the connected device from easyControls (e.g. "26.04.2019").<br><br>

```php
    HELIOS_System_DateFormat_Get(int $InstanceID);
```
Reads the date format set in easyControls (e.g. "mm.dd.yyyy").<br><br>

```php
    HELIOS_System_DaylightSavingTimeMode_Get(int $InstanceID);
```
Reads the current setting for "Daylight Saving Time Mode". It returns a Boolean value.<br><br>

```php
    HELIOS_System_DaylightSavingTimeMode_Set(int $InstanceID, bool $value);
```
Function to de/activate the "Summer time mode" setting.<br>
- TRUE = Daylight saving time mode is active<br>
- FALSE = Daylight saving time mode is inactive<br><br>

```php
    HELIOS_System_FanLevelMin_Get(int $InstanceID);
```
Function to read out the minimum permitted fan level. The answer is an integer value and can be 0 or 1.<br><br>

```php
    HELIOS_System_Language_Get(int $InstanceID);
```
Reads out the language set in easyControls. The country code is returned as a string (e.g. "en").<br><br>

```php
    HELIOS_System_MACAddress_Get(int $InstanceID);
```
Reads the MAC address of the connected system. A string in the format "XX:XX:XX:XX:XX:XX" is returned.<br><br>

```php
    HELIOS_System_Messages_Error_Get(int $InstanceID);
```
Reads all system messages of type "Error" from the connected system and returns an array.<br>
In case of an error, the corresponding array entry does not contain "", but the corresponding error message.<br><br>

```php
    HELIOS_System_Messages_ErrorCount_Get(int $InstanceID);
```
Reads all system messages of type "Error" from the connected system and returns the number of messages as an integer.<br><br>

```php
    HELIOS_System_Messages_Info_Get(int $InstanceID);
```
Reads all system messages of type "Information" from the connected system and returns an array.<br>
In the case of an existing info message, the associated array entry does not contain "", but the corresponding information.<br><br>

```php
    HELIOS_System_Messages_InfoCount_Get(int $InstanceID);
```
Reads all system messages of type "Information" from the connected system and returns the number of messages as an integer.<br><br>

```php
    HELIOS_System_Messages_Warning_Get(int $InstanceID);
```
Reads all system messages of type "Warning" from the connected system and returns an array.<br>
In case of a warning, the corresponding array entry does not contain "", but the corresponding warning.<br><br>

```php
    HELIOS_System_Messages_WarningCount_Get(int $InstanceID);
```
Reads all system messages of type "Warning" from the connected system and returns the number of messages as an integer.<br><br>

```php
    HELIOS_System_Messages_Status_Get(int $InstanceID);
```
Reads all status messages from the connected system and returns an array.<br>
Each active state is located in the corresponding array entry. If a certain status is not active, "" is in the array entry..<br><br>

```php
    HELIOS_System_Messages_Reset(int $InstanceID);
```
Function for resetting all system messages (errors, warnings, ...).<br><br>

```php
    HELIOS_System_Modbus_Get(int $InstanceID);
```
Reads the current setting for "Modbus" from the connected system. It returns a Boolean value.<br><br>

```php
    HELIOS_System_Modbus_Set(int $InstanceID, bool $value);
```
Function to de/activate the setting "Modbus".<br>
- TRUE = Modbus is active<br>
- FALSE = Modbus is inactive<br><br>

```php
    HELIOS_System_OperatingHours_Afterheater_Get(int $InstanceID);
```
Readout of the operating hours (as FLOAT value) of the afterheater.<br><br>

```php
    HELIOS_System_OperatingHours_ExhaustAirFan_Get(int $InstanceID);
```
Readout of the operating hours (as FLOAT value) of the exhaust fan.<br><br>

```php
    HELIOS_System_OperatingHours_Preheater_Get(int $InstanceID);
```
Readout of the operating hours (as FLOAT value) of the preheater.<br><br>

```php
    HELIOS_System_OperatingHours_SupplyAirFan_Get(int $InstanceID);
```
Readout of the operating hours (as FLOAT value) of the supply air fan.<br><br>

```php
    HELIOS_System_OrderNumber_Get(int $InstanceID);
```
Read out the order number of the connected system.<br><br>

```php
    HELIOS_System_ProductionCode_Get(int $InstanceID);
```
Reading the security number of the connected system.<br><br>

```php
    HELIOS_System_SecurityNumber_Get(int $InstanceID);
```
Reading the production code of the connected system.<br><br>

```php
    HELIOS_System_SensorControlSleepMode_Get(int $InstanceID);
```
Reads the current setting for "Sleep mode for sensor control" from the connected system. The answer is a Boolean value.<br><br>

```php
    HELIOS_System_SensorControlSleepMode_Set(int $InstanceID, bool $activate, string $timeFrom, string $timeTo);
```
Function to de/activate or change the setting "Sleep mode for sensor control".<br>
The parameters $timeFrom and $timeTo are for defining the desired rest time and must be transmitted in the format "HH:MM" (e.g. "16:00"). The $activate parameter is for de/activating the setting.<br>
- TRUE = Sleep mode for sensor control is active<br>
- FALSE = Sleep mode for sensor control is inactive<br><br>

```php
    HELIOS_System_SensorControlSleepModeFROM_Get(int $InstanceID);
```
Reads the currently set "Start time" of the "Sleep mode for sensor control" and returns it as a string in the format "HH:MM" (e.g. "19:00).<br><br>

```php
    HELIOS_System_SensorControlSleepModeFROM_Set(int $InstanceID, string $value);
```
Function to set/change the "Start time" of the "Sleep mode for sensor control". The time must be transmitted as a string in the format "HH:MM" (e.g. "19:00).<br>
- If a time is transmitted to easyControls via this function, easyControls automatically activates the "Sleep mode for sensor control"!<br>
- If "0:00" is transmitted for both this function (SensorControlSleepModeFROM) and the 2nd function (SensorControlSleepModeTO), easyControls automatically deactivates the "Sleep mode for sensor control".<br><br>

```php
    HELIOS_System_SensorControlSleepModeTO_Get(int $InstanceID);
```
Reads the currently set "End time" of the "Sleep mode for sensor control" and returns it as a string in the format "HH:MM" (e.g. "19:00).<br><br>

```php
    HELIOS_System_SensorControlSleepModeTO_Set(int $InstanceID, string $value);
```
Function to set/change the "End time" of the "Sleep mode for sensor control". The time must be transmitted as a string in the format "HH:MM" (e.g. "19:00).<br>
- If a time is transmitted to easyControls via this function, easyControls automatically activates the "Sleep mode for sensor control"!<br>
- If "0:00" is transmitted for both this function (SensorControlSleepModeTO) and the 2nd function (SensorControlSleepModeFROM), easyControls automatically deactivates the "Sleep mode for sensor control".<br><br>

```php
    HELIOS_System_SerialNumber_Get(int $InstanceID);
```
Reading the serial number of the connected system.<br><br>

```php
    HELIOS_System_SoftwareUpdateAutomatic_Get(int $InstanceID);
```
Reads the current setting for "Automatic software updates" from the connected system. The answer is a Boolean value.<br><br>

```php
    HELIOS_System_SoftwareUpdateAutomatic_Set(int $InstanceID, bool $value);
```
Function to de/activate the setting "Automatic software updates".<br>
- TRUE = Automatic software updates are active <br>
- FALSE = automatic software updates are inactive<br><br>

```php
    HELIOS_System_SoftwareVersion_Get(int $InstanceID);
```
Reading the software version of the connected system. The version is returned as FLOAT (e.g. 2.27), so that you can work with ">" or "<" in your own scripts.<br><br>

```php
    HELIOS_System_Time_Get(int $InstanceID);
```
Reads the current time from the connected system. The answer is a string in the format "HH:MM:SS" (e.g. "21:10:58").<br><br>

```php
    HELIOS_System_TimezoneGMT_Get(int $InstanceID);
```
Reads the time zone (GMT) set in the connected system. For Germany, the string "2" would be returned.<br><br>

```php
    HELIOS_System_Type_Get(int $InstanceID);
```
Reads the type/model of the connected system and returns it as a string (e.g. "KWL EC 500W R").<br><br>

```php
    HELIOS_Temperature_Comfort_Get(int $InstanceID);
```
Reads the comfort temperature set in the connected system and returns it as FLOAT value (e.g. 17.5).<br><br>

```php
    HELIOS_Temperature_Sensor_Get(int $InstanceID, int $number);
```
Query the current value of a temperature sensor. For $number, the number of the desired sensor must be entered here (1 to max. 7 - depending on the sensors installed).
An array with "Description" (description of the sensor from easyControls UI) and "Value_C" (current temperature in degrees Celsius) is returned.
If the requested sensor is not available, FALSE is returned.<br><br>

```php
    HELIOS_Temperature_Sensors_All_Get(int $InstanceID);
```
Query of all temperature sensors via one function. An array is returned with the sensor numbers 1 to 7 and (if the respective sensor is installed) the description/text and the current temperature in degrees Celsius.<br><br>

```php
    HELIOS_VOCControl_Get(int $InstanceID);
```
Reads the current setting of the VOC control and returns it as an integer value.<br>
- 0 = Off<br>
- 1 = Stepped<br>
- 2 = Stepless<br><br>

```php
    HELIOS_VOCControl_Set(int $InstanceID, int $value);
```
Function for setting the VOC control.<br>
Valid integer values for $value are:<br>
- 0 (Off)<br>
- 1 (Stepped) <br>
- 2 (Stepless)<br><br>

```php
    HELIOS_VOCSensor_Get(int $InstanceID, int $number);
```
Query the current value of a VOC sensor. For $number, the number of the desired sensor must be entered here (1 to max. 8 - depending on the sensors installed).
An array is returned with "Description" (description of the sensor from easyControls UI) and "VOC_ppm". (volatile organic compounds in parts per million).<br>
If the requested sensor is not available, FALSE is returned.<br><br>

```php
    HELIOS_VOCSensors_All_Get(int $InstanceID);
```
Query of all VOC sensors via one function. An array is returned with the sensor numbers 1 to 7 and (if the respective sensor is installed) the description/text and the volatile organic compounds in parts per million (ppm).<br><br>

```php
    HELIOS_WeekProgram_Get(int $InstanceID);
```
Reads the set weekly program from the connected system (from easyControls) as an integer value.<br>
- 0 = Standard 1<br>
- 1 = Standard 2<br>
- 2 = Standard 3<br>
- 3 = User-defined 1<br>
- 4 = User defined 2<br>
- 5 = Off<br>
Please read the section [Notes on the weekly schedule in IP-Symcon and weekly program in easyControls](#53-notes-on-the-weekly-schedule-in-ip-symcon-and-weekly-program-in-easycontrolsbr).<br><br>

```php
    HELIOS_WeekProgram_Set(int $InstanceID, int $value);
```
Function for setting the desired weekly program. The weekly program must be specified as an integer value.<br>
- 0 (Standard 1)<br>
- 1 (Standard 2)<br>
- 2 (Standard 3)<br>
- 3 (User Defined 1)<br>
- 4 (User Defined 2)<br>
- 5 (Off)<br>
Please read the section [Notes on the weekly schedule in IP-Symcon and weekly program in easyControls](#53-notes-on-the-weekly-schedule-in-ip-symcon-and-weekly-program-in-easycontrolsbr).<br><br>

```php
    HELIOS_Update_Data(int $InstanceID);
```  
Reads all information (including system information) about the device from easyControls, returns the data as an array and writes the data into the respective variables.<br><br>

```php
    HELIOS_Update_System_Data(int $InstanceID);
```  
Reads the system information about the device from easyControls, returns the data as an array and writes the data into the respective variables.<br>


<br><br>
## 7. Changelog
Version 1.0:
- First Release


<br><br>
##  8. License
[GNU General Public License v3.0 only](https://www.gnu.org/licenses/gpl-3.0.txt)<br>

Copyright 2019 Christoph Bach<br>

This module is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, version 3.

This module is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.<br>
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this module. If not, see <http://www.gnu.org/licenses/>.