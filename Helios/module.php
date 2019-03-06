<?php

require_once __DIR__ . '/../libs/helper_buffer.php';
require_once __DIR__ . '/../libs/helper_constants.php';
require_once __DIR__ . '/../libs/helper_debug.php';
require_once __DIR__ . '/../libs/helper_variables.php';


if (!defined('CURLTIMEOUTCONNECTMS')) {
    define('CURLTIMEOUTCONNECTMS', 3000);
}
if (!defined('CURLTIMEOUTMS')) {
    define('CURLTIMEOUTMS', 8000);
}
if (!defined('CURLUSERAGENT')) {
    define('CURLUSERAGENT', 'IP-Symcon_' . IPS_GetKernelVersion() . '_' . IPS_GetKernelPlatform() . '_' . IPS_GetKernelRevision());
}


class HELIOS extends IPSModule
{
    use HelperBuffer, HelperDebug, HelperVariables;


    /**
     * Create (internal SDK function)
     *
     */
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('deviceip', '');
        $this->RegisterPropertyString('devicepassword', '');
        $this->RegisterPropertyInteger('timerinterval', 61);
        $this->RegisterPropertyBoolean('show_devicecomponentinformation', true);
        $this->RegisterPropertyBoolean('show_devicesysteminformation', true);
        $this->RegisterPropertyBoolean('show_devicesystemsettings', true);
        $this->RegisterPropertyBoolean('show_deviceimage', true);
        $this->RegisterPropertyInteger('filterremainingdayswarning', 14);
        $this->RegisterPropertyInteger('filterchangeinterval', 6);
        $this->RegisterPropertyString('html_table_width', 'auto');
        $this->RegisterPropertyInteger('html_color_background', -1);
        $this->RegisterPropertyInteger('html_color_title', 14079702);
        $this->RegisterPropertyInteger('html_color_text', 14079702);
        $this->RegisterPropertyInteger('html_fontsize_title', 16);
        $this->RegisterPropertyInteger('html_fontsize_text', 14);
        $this->RegisterPropertyString('weekprogram_0_name', 'Standard 1');
        $this->RegisterPropertyString('weekprogram_1_name', 'Standard 2');
        $this->RegisterPropertyString('weekprogram_2_name', 'Standard 3');
        $this->RegisterPropertyString('weekprogram_3_name', $this->Translate('User defined') . ' 1');
        $this->RegisterPropertyString('weekprogram_4_name', $this->Translate('User defined') . ' 2');
        $this->RegisterPropertyBoolean('log_operatingmode', false);
        $this->RegisterPropertyBoolean('log_operatinghours', false);
        $this->RegisterPropertyBoolean('log_sensors', false);
        $this->RegisterPropertyBoolean('log_fanspeeds', false);
        $this->RegisterPropertyBoolean('log_fanlevel', false);
        $this->RegisterPropertyBoolean('log_messagecount', false);
        $this->RegisterPropertyBoolean('log_heatrecoveryefficiency', false);
        $this->RegisterPropertyBoolean('log_preafterheater', false);
        $this->RegisterPropertyInteger('webfrontinstance_id', 0);
        $this->RegisterPropertyInteger('smtpinstance_id', 0);
        $this->RegisterPropertyInteger('notifscript_id', 0);
        $this->RegisterPropertyBoolean('notif_pushmsg_active', false);
        $this->RegisterPropertyBoolean('notif_mail_active', false);
        $this->RegisterPropertyBoolean('notif_script_active', false);
        $this->RegisterPropertyBoolean('notif_sel_errors', false);
        $this->RegisterPropertyBoolean('notif_sel_warnings', false);
        $this->RegisterPropertyBoolean('notif_sel_filter', false);
        $this->RegisterPropertyBoolean('pingcheckdisabled', false);
        $this->RegisterPropertyBoolean('debug', true);

        if (IPS_GetKernelVersion() >= 5.1) {
            $this->RegisterAttributeString('language', '');
            $this->RegisterAttributeString('MultiBuffer_DataListAll', '');
            $this->RegisterAttributeString('MultiBuffer_KWLdataAR_combined', '');

            // Check attributes for notifications
            $this->RegisterAttributeString('notifsentcheck_filter', '0');
            $this->RegisterAttributeString('notifsentcheck_errors', '0');
            $this->RegisterAttributeString('notifsentcheck_warnings', '0');

            // Attributes for default values of variables
            $this->RegisterAttributeInteger('Attr_OperatingModePresetPartyDuration', 5);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetPartyFanLevel', 4);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetWhisperDuration', 5);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetWhisperFanLevel', 1);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationProgram', 0);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationFanLevel', 1);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationDateStart', time());
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationDateEnd', strtotime('+7 days'));
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationIntervalTime', 3);
            $this->RegisterAttributeInteger('Attr_OperatingModePresetVacationActivationPeriod', 60);

            // Check attributes if default values has been set
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetPartyDuration_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetPartyFanLevel_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetWhisperDuration_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetWhisperFanLevel_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationProgram_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationFanLevel_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationDateStart_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationDateEnd_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationIntervalTime_DONE', false);
            $this->RegisterAttributeBoolean('Attr_OperatingModePresetVacationActivationPeriod_DONE', false);
        }

        // Register timer
        $this->RegisterTimer('Update_BasicInfo', 0, 'HELIOS_Timer_Control($_IPS["TARGET"], "Update_BasicInfo", 2);');
        $this->RegisterTimer('Update_Data', 0, 'HELIOS_Timer_Control($_IPS["TARGET"], "Update_Data", 2);');
        $this->RegisterTimer('FanLevel_Period', 0, 'HELIOS_Timer_Control($_IPS["TARGET"], "FanLevel_Period", 2);');
    }


    /**
     * Destroy (internal SDK function)
     *
     * @return bool
     */
    public function Destroy()
    {
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return parent::Destroy();
        }

        $ModuleInstancesAR = IPS_GetInstanceListByModuleID('{889DFBC4-09A6-4D77-9928-738E5D494362}');
        if (@array_key_exists('0', $ModuleInstancesAR) === false) {
            $VarProfilesAR = array('HELIOS.Bypass', 'HELIOS.CO2VOC.ppm', 'HELIOS.Days', 'HELIOS.DefrostState', 'HELIOS.ErrorNoYes', 'HELIOS.FanLevel', 'HELIOS.FanSpeedRPM', 'HELIOS.FanLevelPercent', 'HELIOS.Filter.Months', 'HELIOS.HeatRecoveryEfficiency', 'HELIOS.Mode', 'HELIOS.ModeDuration', 'HELIOS.ModeIntervalTime', 'HELIOS.ModeActivationPeriod', 'HELIOS.OperatingModeRemainingTime', 'HELIOS.ModeVacationProgram', 'HELIOS.OperatingHours', 'HELIOS.OperatingMode', 'HELIOS.PreAfterheater.Perc.Float', 'HELIOS.PreAfterheater.Perc.Int', 'HELIOS.PreAfterheaterState', 'HELIOS.RelativeHumidity', 'HELIOS.ResetAction', 'HELIOS.StateSwitch', 'HELIOS.Temperature.Indoor', 'HELIOS.Temperature.Outdoor', 'HELIOS.VOCCO2HUMControl', 'HELIOS.WeekProgram');
            foreach ($VarProfilesAR as $VarProfilNameDEL) {
                @IPS_DeleteVariableProfile($VarProfilNameDEL);
            }
        }

        parent::Destroy();

        return true;
    }


    /**
     * ApplyChanges (internal SDK function)
     *
     * @return bool
     */
    public function ApplyChanges()
    {
        // Never delete this line!
        parent::ApplyChanges();

        // Register Kernel Messages
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        // IP-Symcon Kernel ready?
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            $this->SendDebug(__FUNCTION__, 'INFO // ' . $this->Translate('Kernel is not ready! Kernel Runlevel = ') . IPS_GetKernelRunlevel(), 0);
            return false;
        }

        // if all checks are passed, set status "OK"
        if ($this->ConfigCheck() === true) {

            // Write data from attributes to buffer
            $this->Buffer_FillFromAttributes();

            // Set actual device states and information to buffer
            if ($this->BasicInit() === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0015  // ' . $this->Translate('BasicInit failed - ApplyChanges is aborted'), 0, KL_ERROR);
                return false;
            }

            // Create variable profiles and variables
            $this->VariableProfiles_Create(true);
            $this->Variables_Create();

            // Create weekly plan
            $this->WeeklyPlan_Create();

            // Enable/disable logging for variables
            $this->Logging_Set();

            // Create device image
            if ($this->MediaObject_Create() === true) {
                $this->DeviceImage_Get();
            }

            // Start timers
            $this->Timer_Control('Update_BasicInfo', 1);
            $this->Timer_Control('Update_Data', 1);

            // Retrieve all data from easyControls
            $this->Update_Data();
        }

        return false;
    }


    /*** FUNCTIONS FOR INTERNAL USE ***************************************************************************/

    private function BasicInit($return = false)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        // get device language
        $language = $this->GetBuffer('language');
        if ($language === '') {
            $language = $this->System_Language_Get();
            if ($language === false) {
                return false;
            }
        }

        // get all data and write to buffer
        $dataAR_All = $this->Data_List_All();
        if ($dataAR_All !== false) {
            $dataAR_Combined = $this->Data_Combine($dataAR_All);
            $this->SetBufferX('MultiBuffer_KWLdataAR_combined', $dataAR_Combined);

            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, $this->Translate('Basic-Init SUCCESSFUL'), 0, KL_NOTIFY);
            }

            $this->FanLevel_Range_Determine();

            if ($return === true) {
                return $dataAR_Combined;
            }

            return true;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Basic-Init FAILED'), 0, KL_ERROR);

        return false;
    }


    private function Buffer_FillFromAttributes()
    {
        if (IPS_GetKernelVersion() >= 5.1) {
            $attributeAR = array('language');
            foreach ($attributeAR as $attribute) {
                $value = $this->ReadAttributeString($attribute);
                if ($value !== '') {
                    $this->SetBuffer($attribute, $value);
                }
            }
        }

        return true;
    }


    private function Convert_ColorDECtoRGB($dec)
    {
        $hex = ($dec === 0 ? '0' : '');
        while ($dec > 0) {
            $hex = dechex($dec - floor($dec / 16) * 16) . $hex;
            $dec = floor($dec / 16);
        }

        if (strlen($hex) < 6) {
            $diff = 6 - strlen($hex);
            for ($i = 0; $i < $diff; $i++) {
                $hex = '0' . $hex;
            }
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return array($r, $g, $b);
    }


    private function ConfigCheck()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, $this->Translate('The configuration is checked'), 0);
        }

        // validate ip address
        if ($this->ReadPropertyString('deviceip') !== '') {
            if (filter_var($this->ReadPropertyString('deviceip'), FILTER_VALIDATE_IP) === false) {
                $this->InstanceStatus_Set_IfDifferent(201);
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Device IP is not valid'), 0, KL_ERROR);
                return false;
            }
        } else {
            $this->InstanceStatus_Set_IfDifferent(202);
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Device IP is empty'), 0, KL_ERROR);
            return false;
        }

        // validate password
        if ($this->ReadPropertyString('devicepassword') === '') {
            $this->InstanceStatus_Set_IfDifferent(203);
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Device password is empty'), 0, KL_ERROR);
            return false;
        }

        // check timer interval
        $timerinterval = $this->ReadPropertyInteger('timerinterval');
        if (($timerinterval > 0) && ($timerinterval < 3)) {
            $this->InstanceStatus_Set_IfDifferent(204);
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Timer interval is too low (min. 3 seconds)'), 0);
            return false;
        }

        // check notification settings
        if (($this->ReadPropertyBoolean('notif_pushmsg_active') === true) && ($this->ReadPropertyInteger('webfrontinstance_id') === 0)) {
            $this->InstanceStatus_Set_IfDifferent(207);
            return false;
        }
        if (($this->ReadPropertyBoolean('notif_mail_active') === true) && ($this->ReadPropertyInteger('smtpinstance_id') === 0)) {
            $this->InstanceStatus_Set_IfDifferent(208);
            return false;
        }
        if (($this->ReadPropertyBoolean('notif_script_active') === true) && ($this->ReadPropertyInteger('notifscript_id') === 0)) {
            $this->InstanceStatus_Set_IfDifferent(209);
            return false;
        }

        if ($this->System_Language_Get() === false) {
            $this->InstanceStatus_Set_IfDifferent(205);
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('The configuration could not be checked successfully'), 0, KL_ERROR);
            return false;
        }

        // set status 102/OK
        $this->InstanceStatus_Set_IfDifferent(102);

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, $this->Translate('The configuration was checked successfully'), 0, KL_NOTIFY);
        }

        return true;
    }


    private function curl_GET($url)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($this->ReadPropertyBoolean('pingcheckdisabled') === false) {
            if (@Sys_Ping($this->ReadPropertyString('deviceip'), 3000) === false) {
                $this->InstanceStatus_Set_IfDifferent(206);
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0012 // ' . $this->Translate('Ping failed //No network connection to easyControls'), 0, KL_ERROR);
                return false;
            }
            $this->InstanceStatus_Set_IfDifferent(102);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, CURLUSERAGENT);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, CURLTIMEOUTCONNECTMS);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, CURLTIMEOUTMS);

        if (!$curlData = curl_exec($ch)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0001 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
            return false;
        }

        if (strpos($curlData, '403') !== false) {
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Not logged in - Login will be executed'), 0);
            }
            $LoginState = $this->Login_easyControlsGUI($ch);

            if ($LoginState === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0002 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
                return false;
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, CURLUSERAGENT);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, CURLTIMEOUTCONNECTMS);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, CURLTIMEOUTMS);

            if (!$curlData = curl_exec($ch)) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0003 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
                return false;
            }
        }

        curl_close($ch);

        if (strpos($curlData, '404') !== false) {
            if ((strpos($url, '/dl2_') !== false) || (strpos($url, '/lab2_') !== false) || (strpos($url, '/dl7_') !== false) || (strpos($url, '/dl10_') !== false) || (strpos($url, '/dl16_') !== false)) {
                // SKIP ERROR MESSAGE
            } else {
                $Error = trim($curlData);
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0004 // URL (' . $url . ') ' . $this->Translate('not found') . ' - ' . $Error, 0);
                return false;
            }
        }

        $result = @json_decode(@json_encode(@simplexml_load_string($curlData)), true);
        if ($result === false) {
            return trim($curlData);
        }

        return $result;
    }


    private function curl_POST($postData, $refererURL, $xml = false)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $host = $this->ReadPropertyString('deviceip');

        if ($this->ReadPropertyBoolean('pingcheckdisabled') === false) {
            if (@Sys_Ping($host, 3000) === false) {
                $this->InstanceStatus_Set_IfDifferent(206);
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0012 // ' . $this->Translate('Ping failed // easyControls is not accessible via the network'), 0, KL_ERROR);
                IPS_LogMessage('easyControls--' . __FUNCTION__, $this->Translate('ERROR') . ' // 0x0012 // ' . $this->Translate('Ping failed // easyControls is not accessible via the network'));
                return false;
            }
            $this->InstanceStatus_Set_IfDifferent(102);
        }

        $baseURL = 'http://' . $host;

        if ($xml === false) {
            $curlURL = $refererURL;
        } else {
            $curlURL = $baseURL . '/data/' . $xml;
        }

        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Content-Length: ' . strlen($postData);
        $headers[] = 'Content-Type: text/plain;charset=UTF-8';
        $headers[] = 'Host: ' . $host;
        $headers[] = 'Origin: ' . $baseURL;
        $headers[] = 'Referer: ' . $refererURL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curlURL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERAGENT, CURLUSERAGENT);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, CURLTIMEOUTCONNECTMS);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, CURLTIMEOUTMS);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if (!$curlData = curl_exec($ch)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0005 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
            return false;
        }

        if (strpos($curlData, '403') !== false) {
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Not logged in - Login will be executed'), 0);
            }
            $LoginState = $this->Login_easyControlsGUI($ch);

            if ($LoginState === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0006 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
                return false;
            }

            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Login OK - Data is transfered'), 0);
            }

            curl_setopt($ch, CURLOPT_URL, $curlURL);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_USERAGENT, CURLUSERAGENT);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, CURLTIMEOUTCONNECTMS);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, CURLTIMEOUTMS);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            if (!$curlData = curl_exec($ch)) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0007 // ' . $this->Translate('Data could not be transferred to the device'), 0, KL_ERROR);
                curl_close($ch);
                return false;
            }
        }

        if (strpos($curlData, 'easyControls: Basic Modul SD-Card Error:4') !== false) {
            $response = trim($curlData);
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'DEBUG // URL = ' . $curlURL . ' // ' . $this->Translate('Data') . ' = ' . $postData . ' // ' . $this->Translate('Response') . ' = ' . $response, 0);
            }
        }

        if (strpos($curlData, '<!DOCTYPE HTML PUBLIC') !== false) {
            return true;
        }

        return @json_decode(@json_encode(@simplexml_load_string($curlData)), true);
    }


    private function Data_Combine($RawAR)
    {
        $lang = $this->GetBuffer('language');
        if ($lang === '') {
            $lang = 'de';
        }

        $ResultAR = array();
        $rcount = 0;
        foreach ($RawAR as $RawIndex => $RawValue) {
            $FilenameLAB = 'lab' . $RawIndex . '_' . $lang . '.xml';
            $FilenameW = 'werte' . $RawIndex . '.xml';

            if (@isset($RawAR[$RawIndex][$FilenameLAB]['ID'])) {

                // Array der labX_Y.xml zur Suche vorbereiten
                $SearchAR_LABx = $RawAR[$RawIndex][$FilenameLAB]['ID'];
                $SearchAR_LAB = array();
                foreach ($SearchAR_LABx as $entry) {
                    $SearchAR_LAB[] = (string)$entry;
                }

                // Array der werteX_Y.xml zur Suche vorbereiten
                if (@array_key_exists($FilenameW, $RawAR[$RawIndex]) === false) {
                    $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0030 // ' . $this->Translate('Problem combining the following file') . ': ' . $FilenameW, 0, KL_ERROR);
                    continue;
                }
                $SearchAR_WERTEx = $RawAR[$RawIndex][$FilenameW]['ID'];
                $SearchAR_WERTE = array();
                foreach ($SearchAR_WERTEx as $entry) {
                    $SearchAR_WERTE[] = (string)$entry;
                }

                foreach ($RawAR[$RawIndex][$FilenameLAB]['ID'] as $LAB_ID_Value) {
                    if ((@is_array($SearchAR_LAB)) && (@is_array($SearchAR_WERTE))) {
                        $SearchIndexLabID = @array_search((string)$LAB_ID_Value, $SearchAR_LAB, false);
                        $SearchIndexWerteID = @array_search((string)$LAB_ID_Value, $SearchAR_WERTE, false);

                        if (($SearchIndexLabID !== false) && ($SearchIndexWerteID !== false)) {
                            $Text = (string)$RawAR[$RawIndex][$FilenameLAB]['VL'][$SearchIndexLabID];
                            if (substr($Text, -1) === ':') {
                                $Text = substr($Text, 0, -1);
                            }

                            $Value = (string)$RawAR[$RawIndex][$FilenameW]['VA'][$SearchIndexWerteID];
                            if (substr($Value, -4) === '.hGP') {
                                $Value = substr($Value, 0, -4);
                            }

                            $ResultAR[$rcount]['ID'] = (string)$LAB_ID_Value;
                            $ResultAR[$rcount]['File'] = $FilenameW;
                            $ResultAR[$rcount]['Text'] = $Text;
                            $ResultAR[$rcount]['Value'] = $Value;
                            $rcount++;
                        }
                    }
                }
            }
        }

        return $ResultAR;
    }


    private function Data_Get_LabDlXML($requestData)
    {
        $host = $this->ReadPropertyString('deviceip');
        $lang = $this->GetBuffer('language');
        if ($lang === '') {
            $lang = 'de';
        }

        $baseURL = 'http://' . $host;
        if (strpos('&', $requestData) !== false) {
            $requestData = str_replace('&', $lang, $requestData);
        }

        $url = $baseURL . '/data/' . $requestData;

        return $this->curl_GET($url);
    }


    private function Data_Get_Werte($file)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        preg_match('|.*\D(\d\d?)\D.*|', $file, $matchxmlnr);
        if (@array_key_exists('1', $matchxmlnr) === true) {
            $refererURL = $this->Map_XMLnr_RefererURL((int)$matchxmlnr[1]);

            $resultAR = $this->curl_POST('xml=/data/' . $file, $refererURL);

            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'DEBUG // resultAR = ' . $this->DataToString($resultAR) . ' // refererURL = ' . $refererURL, 0);
            }

            return $resultAR;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0009 // ' . $this->Translate('RefererURL could not be determined'), 0, KL_ERROR);
        return false;
    }


    private function Data_List_All()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $time_start_code = microtime(true);
        }

        $lang = $this->GetBuffer('language');
        if ($lang === '') {
            $lang = 'de';
        }

        $NrAR = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '16');
        $FileAR = array('dl§_&.xml', 'lab§_&.xml', 'werte§.xml');

        $DataListAll_AR = array();
        foreach ($NrAR as $Nr) {
            foreach ($FileAR as $File) {
                $requestData = str_replace('§', $Nr, $File);
                $requestData = str_replace('&', $lang, $requestData);

                if (strpos($requestData, 'werte') === 0) {
                    $result = $this->Data_Get_Werte($requestData);
                } else {
                    $result = $this->Data_Get_LabDlXML($requestData);
                }

                if ($result !== false) {
                    $DataListAll_AR[$Nr][$requestData] = $result;
                } else {
                    continue;
                }
            }
        }

        if ($DebugActive === true) {
            $duration_code = round(microtime(true) - $time_start_code, 2);
            $this->SendDebug(__FUNCTION__, $this->Translate('DURATION') . ' = ' . $duration_code . ' ' . $this->Translate('seconds'), 0);
        }

        if (@count($DataListAll_AR) > 0) {
            $this->SetBufferX('MultiBuffer_DataListAll', $DataListAll_AR);
            return $DataListAll_AR;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0012 // ' . $this->Translate('There was a problem collecting the basic data'), 0, KL_ERROR);

        return false;
    }


    /**
     * DataToBool (check string and return as bool)
     *
     * @param $data
     * @return bool
     */
    private function DataToBool($data)
    {
        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }


    /**
     * DataToString (check data and return as string)
     *
     * @param $data
     * @return string
     */
    private function DataToString($data)
    {
        if (is_bool($data) === true) {
            $result = $data ? 'TRUE' : 'FALSE';
        } elseif (is_array($data) === true) {
            $result = serialize($data);
        } elseif (is_object($data) === true) {
            $result = serialize($data);
        } elseif ($data === NULL) {
            $result = 'NULL';
        } else {
            $result = (string)$data;
        }

        return $result;
    }


    /**
     * DateFormat_Check (checks whether a date exists in the format required by the KWL and is generally valid)
     *
     * @param $date
     * @return bool
     */
    private function DateFormat_Check($date)
    {
        $dateFormatBuffer = $this->GetBuffer('DateFormat');
        if ($dateFormatBuffer !== '') {
            $dateFormatNEED = $dateFormatBuffer;
        } else {
            $dateFormatNEED = $this->System_DateFormat_Get();
        }

        $dateFormat = str_replace(array('dd', 'mm', 'yyyy'), array('d', 'm', 'Y'), $dateFormatNEED);

        $dateOBJ = date_parse_from_format($dateFormat, $date);
        return checkdate($dateOBJ['month'], $dateOBJ['day'], $dateOBJ['year']) === true;
    }


    /**
     * @return array|false
     */
    private function FanLevel_Range_Determine()
    {
        $optAR = $this->Map_ID_to_OPT('v00930');

        if ($optAR !== false) {
            $optARint = array();
            foreach ($optAR as $optEntry) {
                $optARint[] = (int)$optEntry;
            }

            $FanLevelMIN = $this->System_FanLevelMin_Get();
            if ($FanLevelMIN !== NULL) {
                $resultAR['min'] = $FanLevelMIN;
            } else {
                $resultAR['min'] = min($optARint);
            }
            $resultAR['max'] = max($optARint);

            if ($resultAR['min'] < $resultAR['max']) {
                $this->SetBuffer('FanLevelMAX', $resultAR['max']);
                return $resultAR;
            }
        }

        return false;
    }


    private function FeatureCheck($id)
    {
        $dataAR = $this->Search_DataAR($id, 'ID');

        if (@array_key_exists('Value', $dataAR) === true) {
            if ($dataAR['Value'] !== '-') {
                $this->SetBuffer($id, '1');
                return true;
            }
        } else {
            $file = $this->Map_ID_to_File($id);
            $dataAR = $this->Data_Get_Werte($file);
            $result = $this->Map_ID_to_VA($dataAR, $id);
            if (($result !== false) && ($result !== '-')) {
                $this->SetBuffer($id, '1');
                return true;
            }
        }

        $this->SetBuffer($id, '0');
        return false;
    }


    private function File_Cache($fileName, $fileContent = false, $milliseconds = false)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $fileBufferTS = (float)$this->GetBufferX($fileName . '_TS');
        if (($fileContent === false) && ($fileBufferTS > 0)) {
            if (microtime(true) <= $fileBufferTS) {
                return $this->GetBufferX($fileName . '_DATA');
            }
        }

        if ($milliseconds !== false) {
            $validTime = microtime(true) + ($milliseconds / 1000);

            if ($fileContent !== false) {
                $this->SetBufferX($fileName . '_TS', $validTime);
                return $this->SetBufferX($fileName . '_DATA', $fileContent);
            }
        }

        return false;
    }


    private function FunctionHelperGET($id, $function, $liveQuery = false)
    {
        if ($liveQuery === true) {
            $file = $this->Map_ID_to_File($id);
            $fileCache = $this->File_Cache($file);
            if ($fileCache === false) {
                $dataAR = $this->Data_Get_Werte($file);
                $this->File_Cache($file, $dataAR, 2000);
            } else {
                $dataAR = $fileCache;
            }

            $result = $this->Map_ID_to_VA($dataAR, $id);

            if ($result !== false) {
                return $result;
            }
        } else {
            $dataAR = $this->Search_DataAR($id, 'ID');

            if (@array_key_exists('Value', $dataAR) === true) {
                if ($dataAR['Value'] !== '-') {
                    return $dataAR['Value'];
                }
            }
        }

        if ($id === 'v02152') {
            $file = $this->Map_ID_to_File($id);
            $fileCache = $this->File_Cache($file);
            if ($fileCache === false) {
                $dataAR = $this->Data_Get_Werte($file);
                $this->File_Cache($file, $dataAR, 2000);
            } else {
                $dataAR = $fileCache;
            }

            $result = $this->Map_ID_to_VA($dataAR, $id);

            if ($result !== false) {
                return $result;
            }
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0011 // ' . $function . ' // ' . $this->Translate('No data could be retrieved') . ' // ID = ' . $id . ' // dataAR = ' . $this->DataToString($dataAR), 0, KL_ERROR);

        return NULL;
    }


    private function FunctionHelperSET($vID, $value)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $postData = $vID . '=' . $value;
        $xml = $this->Map_ID_to_File($vID);
        preg_match('|(\d+)|', $xml, $matchXMLnr);
        if (@array_key_exists('1', $matchXMLnr) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0027 // ' . $this->Translate('Passed vID could not be assigned to an XML file') . ' // vID = ' . $vID, 0, KL_ERROR);
            return false;
        }
        $refererURL = $this->Map_XMLnr_RefererURL((int)$matchXMLnr[1]);

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'DEBUG // xml = ' . $xml . ' // postData = ' . $postData . ' // refererURL = ' . $refererURL, 0);
        }

        return $this->curl_POST($postData, $refererURL, $xml);
    }


    private function FunctionHelperSETcustom($fileName, $postData)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $host = $this->ReadPropertyString('deviceip');

        $refererURL = 'http://' . $host . '/' . $fileName;

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'DEBUG // postData = ' . $postData . ' // refererURL = ' . $refererURL, 0);
        }

        return $this->curl_POST($postData, $refererURL);
    }


    /**
     * GetConfigurationForm (dynamic generation of form data for the module instance)
     *
     * @return string
     */
    public function GetConfigurationForm()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $FormData = '{
	"elements":
	[
		{ "type": "Label", "label": "##### Helios easyControls v0.9 #####" },
		{ "type": "Label", "label": "##### 06.03.2019 - 16:30 #####"},
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "ValidationTextBox", "name": "deviceip", "caption": "Device IP-Address" },
		{ "type": "PasswordTextBox", "name": "devicepassword", "caption": "Device Password" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "NumberSpinner", "name": "timerinterval", "caption": "Update interval (seconds)" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "CheckBox", "name": "show_devicecomponentinformation", "caption": "Show variables with device component information" },
		{ "type": "CheckBox", "name": "show_devicesysteminformation", "caption": "Show variables with device information" },
		{ "type": "CheckBox", "name": "show_devicesystemsettings", "caption": "Show variables with device settings" },
		{ "type": "CheckBox", "name": "show_deviceimage", "caption": "Show image of device" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "Label", "label": "X days before a due filter change, set the state variable to -warning-:" },
		{ "type": "NumberSpinner", "name": "filterremainingdayswarning", "caption": "Filter-Warning" },
		{ "type": "NumberSpinner", "name": "filterchangeinterval", "caption": "Filter-Wechsel-Intervall (Monate)" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "Label", "label": "Settings for the visual display of status/error/warning/info messages:" },
		{ "type": "ValidationTextBox", "name": "html_table_width", "caption": "Table width" },
		{ "type": "SelectColor", "name": "html_color_background", "caption": "Background color", "suffix" : "HEX" },
        { "type": "SelectColor", "name": "html_color_title", "caption": "Title color", "suffix" : "HEX" },
        { "type": "SelectColor", "name": "html_color_text", "caption": "Text color", "suffix" : "HEX" },
        { "type": "NumberSpinner", "name": "html_fontsize_title", "caption": "Font size title", "suffix" : "px" },
        { "type": "NumberSpinner", "name": "html_fontsize_text", "caption": "Font size text", "suffix" : "px" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "Label", "label": "Here you can replace the predefined names of the week programs with your own text:" },
		{ "type": "ValidationTextBox", "name": "weekprogram_0_name", "caption": "Week program 0" },
		{ "type": "ValidationTextBox", "name": "weekprogram_1_name", "caption": "Week program 1" },
		{ "type": "ValidationTextBox", "name": "weekprogram_2_name", "caption": "Week program 2" },
		{ "type": "ValidationTextBox", "name": "weekprogram_3_name", "caption": "Week program 3" },
		{ "type": "ValidationTextBox", "name": "weekprogram_4_name", "caption": "Week program 4" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
		{ "type": "Label", "label": "Enable/disable logging for the following areas:" },
		{ "type": "CheckBox", "name": "log_operatingmode", "caption": "Operating modes" },
		{ "type": "CheckBox", "name": "log_operatinghours", "caption": "Operating hours of individual components" },
		{ "type": "CheckBox", "name": "log_sensors", "caption": "Sensors (CO2, humidity, temperature, VOC)" },
		{ "type": "CheckBox", "name": "log_fanspeeds", "caption": "Fan speeds" },
		{ "type": "CheckBox", "name": "log_fanlevel", "caption": "Fan levels" },
		{ "type": "CheckBox", "name": "log_messagecount", "caption": "Number of error/info/warning messages" },
		{ "type": "CheckBox", "name": "log_heatrecoveryefficiency", "caption": "Heat recovery efficiency" },
		{ "type": "CheckBox", "name": "log_preafterheater", "caption": "Pre-/Afterheater (actual power and heat output delivered)" },
		{ "type": "Label", "label": "___________________________________________________________________________________________" },
        { "type": "Label", "label": "WebFront instance used to send push messages (valid IPS subscription required):" },
        { "type": "SelectInstance", "name": "webfrontinstance_id", "caption": "WebFront-Instance" },
        { "type": "CheckBox", "name": "notif_pushmsg_active", "caption": "Push notification" },
        { "type": "Label", "label": "SMTP-Instance used to send the e-mail messages:" },
        { "type": "SelectInstance", "name": "smtpinstance_id", "caption": "SMTP-Instance" },
        { "type": "CheckBox", "name": "notif_mail_active", "caption": "EMail notification" },
        { "type": "Label", "label": "Script for own notification actions (LED flashing, SMS, voice output, ...):" },
        { "type": "SelectScript", "name": "notifscript_id", "caption": "Script" },
        { "type": "CheckBox", "name": "notif_script_active", "caption": "Own script" },
        { "type": "Label", "label": "___________________________________________________________________________________________" },
        { "type": "Label", "label": "Here you can de/activate notifications for different topics:" },
        { "type": "CheckBox", "name": "notif_sel_errors", "caption": "Error messages in the system" },
        { "type": "CheckBox", "name": "notif_sel_warnings", "caption": "Warning messages in the system" },
        { "type": "CheckBox", "name": "notif_sel_filter", "caption": "Filter change required" },
        { "type": "Label", "label": "___________________________________________________________________________________________" },
        { "type": "Label", "label": "Setting options for the IP-Symcon module itself:" },
		{ "type": "CheckBox", "name": "pingcheckdisabled", "caption": "Disable ping checking when connecting" },
		{ "type": "CheckBox", "name": "debug", "caption": "Debug" }
	],
	"actions":
	[

		{ "type": "Button", "label": "Operating mode MANU", "onClick": "HELIOS_OperatingMode_Set($id, \'manual\');" },
		{ "type": "Button", "label": "Operating mode AUTO", "onClick": "HELIOS_OperatingMode_Set($id, \'automatic\');" },
		{ "type": "Label", "label": "___________________________________________________________________________" },
		{ "type": "Button", "label": "Fan level 1 (Moisture protection)", "onClick": "HELIOS_FanLevel_Set($id, 1);" },
		{ "type": "Button", "label": "Fan level 2 (Reduced ventilation)", "onClick": "HELIOS_FanLevel_Set($id, 2);" },
		{ "type": "Button", "label": "Fan level 3 (Nominal ventilation)", "onClick": "HELIOS_FanLevel_Set($id, 3);" },
		{ "type": "Button", "label": "Fan level 4 (Intensive ventilation)", "onClick": "HELIOS_FanLevel_Set($id, 4);" },
		{ "type": "Label", "label": "___________________________________________________________________________" },
		{ "type": "Button", "label": "Filter - Reset", "onClick": "HELIOS_Filter_Reset($id);" },
		{ "type": "Button", "label": "Messages - Reset", "onClick": "HELIOS_System_Messages_Reset($id);" },
		{ "type": "Label", "label": "___________________________________________________________________________" },
		{ "type": "Button", "label": "Send Test-Notification", "onClick": "HELIOS_Notification_Test($id);" },
		{ "type": "Label", "label": "___________________________________________________________________________" },
		{ "type": "Button", "caption": "Module Documentation", "onClick": "echo \'https://www.bayaro.net\'" },
		{ "type": "Button", "caption": "www.heliosventilatoren.de", "onClick": "echo \'https://www.heliosventilatoren.de\'" },
		{ "type": "Button", "caption": "www.bayaro.net", "onClick": "echo \'https://www.bayaro.net\'" }

	],
	"status":
	[
		{ "code": 101, "icon": "active", "caption": "Creating instance" },
		{ "code": 102, "icon": "active", "caption": "OK" },
		{ "code": 201, "icon": "error", "caption": "ERROR - IP address is not valid" },
		{ "code": 202, "icon": "error", "caption": "ERROR - IP address is empty" },
		{ "code": 203, "icon": "error", "caption": "ERROR - Password is empty" },
		{ "code": 204, "icon": "error", "caption": "ERROR - Timer interval is not valid. Smallest valid value is 3 seconds" },
		{ "code": 205, "icon": "error", "caption": "ERROR - Login to easyControls failed" },
		{ "code": 206, "icon": "error", "caption": "ERROR - easyControls is not accessible via the network" },
		{ "code": 207, "icon": "inactive", "caption": "ERROR - A WebFront instance must be selected for push messages" },
		{ "code": 208, "icon": "inactive", "caption": "ERROR - A email instance must be selected for email messages" },
		{ "code": 209, "icon": "inactive", "caption": "ERROR - A script must be selected for a own script action" }
	]
}';

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'FormData = ' . $FormData, 0);
        }

        return $FormData;
    }


    /**
     * InstanceStatus_Set_IfDifferent (extension to internal SDK function)
     *
     * @param $Status
     * @return bool
     */
    private function InstanceStatus_Set_IfDifferent($Status)
    {
        $result = true;
        $ParentInstanceInfoAR = IPS_GetInstance($this->InstanceID);
        if ($ParentInstanceInfoAR['InstanceStatus'] !== $Status) {
            $result = $this->SetStatus($Status);
        }

        return $result;
    }


    private function Logging_Set()
    {
        $ArchiveHandlerID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];

        $dataAR['log_fanlevel']['enableLogging'] = $this->ReadPropertyBoolean('log_fanlevel');
        $dataAR['log_fanspeeds']['enableLogging'] = $this->ReadPropertyBoolean('log_fanspeeds');
        $dataAR['log_heatrecoveryefficiency']['enableLogging'] = $this->ReadPropertyBoolean('log_heatrecoveryefficiency');
        $dataAR['log_messagecount']['enableLogging'] = $this->ReadPropertyBoolean('log_messagecount');
        $dataAR['log_operatingmode']['enableLogging'] = $this->ReadPropertyBoolean('log_operatingmode');
        $dataAR['log_operatinghours']['enableLogging'] = $this->ReadPropertyBoolean('log_operatinghours');
        $dataAR['log_preafterheater']['enableLogging'] = $this->ReadPropertyBoolean('log_preafterheater');
        $dataAR['log_sensors']['enableLogging'] = $this->ReadPropertyBoolean('log_sensors');

        $dataAR['log_fanlevel']['varIdentAR'] = array('FanLevel', 'FanLevelPercent');
        $dataAR['log_fanspeeds']['varIdentAR'] = array('FanSpeedExtractAir', 'FanSpeedSupplyAir');
        $dataAR['log_heatrecoveryefficiency']['varIdentAR'] = array('HeatRecoveryEfficiency');
        $dataAR['log_messagecount']['varIdentAR'] = array('SystemErrorCount', 'SystemInfoCount', 'SystemWarningCount');
        $dataAR['log_operatingmode']['varIdentAR'] = array('OperatingMode');
        $dataAR['log_operatinghours']['varIdentAR'] = array('OperatingHours', 'OperatingHoursExtractAirFan', 'OperatingHoursPreheater', 'OperatingHoursAfterheater', 'OperatingHoursSupplyAirFan');
        $dataAR['log_preafterheater']['varIdentAR'] = array('AfterheaterState', 'PreheaterState');
        $dataAR['log_sensors']['varIdentAR'] = array('TemperatureOutdoorAir', 'TemperatureSupplyAir', 'TemperatureExhaustAir', 'TemperatureExhaustAir', 'TemperatureDuctOutdoorAir', 'TemperatureDuctSupplyAir', 'TemperatureReturnWWRegister', 'SensorCO2_1', 'SensorCO2_2', 'SensorCO2_3', 'SensorCO2_4', 'SensorCO2_5', 'SensorCO2_6', 'SensorCO2_7', 'SensorCO2_8', 'SensorHumidityRH_1', 'SensorHumidityRH_2', 'SensorHumidityRH_3', 'SensorHumidityRH_4', 'SensorHumidityRH_5', 'SensorHumidityRH_6', 'SensorHumidityRH_7', 'SensorHumidityRH_8', 'SensorHumidityTC_1', 'SensorHumidityTC_2', 'SensorHumidityTC_3', 'SensorHumidityTC_4', 'SensorHumidityTC_5', 'SensorHumidityTC_6', 'SensorHumidityTC_7', 'SensorHumidityTC_8', 'SensorVOC_1', 'SensorVOC_2', 'SensorVOC_3', 'SensorVOC_4', 'SensorVOC_5', 'SensorVOC_6', 'SensorVOC_7', 'SensorVOC_8');

        foreach ($dataAR as $dataARsub) {
            foreach ($dataARsub['varIdentAR'] as $varIdent) {
                $varID = @$this->GetIDForIdent($varIdent);
                if ($varID > 0) {
                    @AC_SetLoggingStatus($ArchiveHandlerID, $varID, $dataARsub['enableLogging']);
                }
            }
        }

        $result = true;
        if (IPS_HasChanges($ArchiveHandlerID) === true) {
            $result = @IPS_ApplyChanges($ArchiveHandlerID);
        }

        return $result;
    }


    private function Login_easyControlsGUI($ch)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $host = $this->ReadPropertyString('deviceip');
        $password = $this->ReadPropertyString('devicepassword');

        $post_data = array();
        $post_data['v00402'] = $password;
        $postfields = http_build_query($post_data);

        $loginReferer = 'http://' . $host . '/index.htm';
        $targetURL = 'http://' . $host . '/info.htm';

        curl_setopt($ch, CURLOPT_URL, $targetURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_REFERER, $loginReferer);
        curl_setopt($ch, CURLOPT_USERAGENT, CURLUSERAGENT);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, CURLTIMEOUTCONNECTMS);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, CURLTIMEOUTMS);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);


        if (!$result = curl_exec($ch)) {
            if (strpos(curl_error($ch), '403') !== false) {
                $this->InstanceStatus_Set_IfDifferent(205);
            }
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Login failed with error: ') . curl_error($ch), 0, KL_ERROR);
            return false;
        }

        $this->InstanceStatus_Set_IfDifferent(102);

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Login successfully completed'), 0);
        }

        return $ch;
    }


    private function Map_ID_to_File($id)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        // werte1.xml
        $werte1AR = array('v01306', 'v00000', 'v00001', 'v00002', 'v00003', 'v00004', 'v00005', 'v00006', 'v00007', 'v00008', 'v00012', 'v00013', 'v00014', 'v00015', 'v00016', 'v00017', 'v00018', 'v00019', 'v00020', 'v00021', 'v00022', 'v00023', 'v00024', 'v00025', 'v00026', 'v00027', 'v00028', 'v00029', 'v00030', 'v00031', 'v00032', 'v00033', 'v00034', 'v00035', 'v00036', 'v00037', 'v00038', 'v00039', 'v00040', 'v00041', 'v00042', 'v00043', 'v00051', 'v00052', 'v00053', 'v01017', 'v02103', 'v01071', 'v01072', 'v01073', 'v01074', 'v01075', 'v01076', 'v01077', 'v01078', 'v02020', 'v02021', 'v02022', 'v02023', 'v02024', 'v02025', 'v02026', 'v02027', 'v02116', 'v02134', 'v02137', 'v02142', 'v02143', 'v02144', 'v02145', 'v02146', 'v02147', 'v02148', 'v02149', 'v02150', 'v02151', 'v02152');
        $search1 = @array_search($id, $werte1AR, false);
        if ($search1 !== false) {
            $file = 'werte1.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte4.xml
        $werte4AR = array('v01306', 'v00000', 'v00001', 'v00002', 'v00093', 'v00094', 'v00098', 'v00099', 'v00101', 'v00102', 'v00103', 'v00601', 'v00604', 'v01020');
        $search4 = @array_search($id, $werte4AR, false);
        if ($search4 !== false) {
            $file = 'werte4.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte8.xml
        $werte8AR = array('v01306', 'v00024', 'v00033', 'v00037', 'v00040', 'v00092', 'v00094', 'v00097', 'v00099', 'v00101', 'v00102', 'v00103', 'v00104', 'v00105', 'v00106', 'v00107', 'v00108', 'v00109', 'v00110', 'v00111', 'v00112', 'v00113', 'v00114', 'v00115', 'v00116', 'v00117', 'v00118', 'v00119', 'v00120', 'v00121', 'v00122', 'v00123', 'v00124', 'v00125', 'v00126', 'v00128', 'v00129', 'v00130', 'v00131', 'v00132', 'v00133', 'v00134', 'v00135', 'v00136', 'v00137', 'v00138', 'v00139', 'v00140', 'v00141', 'v00142', 'v00143', 'v00144', 'v00146', 'v00201', 'v00348', 'v00349', 'v00601', 'v00602', 'v01020', 'v01050', 'v01051', 'v01300', 'v01301', 'v01302', 'v01071', 'v01072', 'v01073', 'v01074', 'v01075', 'v01076', 'v01077', 'v01078', 'v01081', 'v01082', 'v01083', 'v01084', 'v01085', 'v01086', 'v01087', 'v01088', 'v01091', 'v01092', 'v01093', 'v01094', 'v01095', 'v01096', 'v01097', 'v01098', 'v02020', 'v02021', 'v02022', 'v02023', 'v02024', 'v02025', 'v02026', 'v02027', 'v02117', 'v02118', 'v02119', 'v02136', 'v02137', 'v02142', 'v02152');
        $search8 = @array_search($id, $werte8AR, false);
        if ($search8 !== false) {
            $file = 'werte8.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte3.xml
        $werte3AR = array('v01306', 'v00091', 'v00092', 'v00093', 'v00094', 'v00096', 'v00097', 'v00098', 'v00099');
        $search3 = @array_search($id, $werte3AR, false);
        if ($search3 !== false) {
            $file = 'werte3.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte6.xml
        $werte6AR = array('v01306', 'v00601', 'v00602', 'v00603', 'v00604', 'v00605', 'v00606', 'v01050', 'v01051');
        $search6 = @array_search($id, $werte6AR, false);
        if ($search6 !== false) {
            $file = 'werte6.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte11.xml
        $werte11AR = array('v01306', 'v00003', 'v00004', 'v00005', 'v00006', 'v00007', 'v00008', 'v00051', 'v00052', 'v00348', 'v00349', 'v01101', 'v01102', 'v01103', 'v01104', 'v01105', 'v01106', 'v01108', 'v01109', 'v02103', 'v02116', 'v02134');
        $search11 = @array_search($id, $werte11AR, false);
        if ($search11 !== false) {
            $file = 'werte11.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte12.xml
        $werte12AR = array('v01306', 'v00021', 'v00022', 'v00023', 'v01010', 'v00024', 'v00201', 'v01017', 'v01019', 'v01020', 'v01021', 'v00053', 'v01031', 'v01032', 'v01033', 'v01035', 'v01036', 'v01037', 'v01041', 'v01042', 'v01200', 'v02115', 'v02120', 'v02121', 'v02128', 'v02129');
        $search12 = @array_search($id, $werte12AR, false);
        if ($search12 !== false) {
            $file = 'werte12.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte16.xml
        $werte16AR = array('v01306', 'v02104', 'v01300', 'v01301', 'v01302', 'v01303', 'v01304', 'v01305');
        $search16 = @array_search($id, $werte16AR, false);
        if ($search16 !== false) {
            $file = 'werte16.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // werte11.xml
        $werte11AR = array('v01306', 'v00003', 'v00004', 'v00005', 'v00006', 'v00007', 'v00008', 'v00051', 'v00052', 'v00348', 'v00349', 'v01101', 'v01102', 'v01103', 'v01104', 'v01105', 'v01106', 'v01108', 'v01109', 'v02103', 'v02116', 'v02134');
        $search11 = @array_search($id, $werte11AR, false);
        if ($search11 !== false) {
            $file = 'werte11.xml';
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $file, 0);
            }

            return $file;
        }

        // search combined array
        $dataAR = $this->Search_DataAR($id, 'ID');
        if (@array_key_exists('File', $dataAR) === true) {
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // ' . $this->Translate('File') . ' = ' . $dataAR['File'], 0);
            }

            return $dataAR['File'];
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Mapping failed') . ' // ID = ' . $id . ' // Array = ' . $this->DataToString($dataAR), 0, KL_ERROR);

        return false;
    }


    /**
     * Map_ID_to_OPT (xxxxxxx)
     *
     * @param $id
     * @return array|false
     */
    private function Map_ID_to_OPT($id)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $dataAR = $this->GetBufferX('MultiBuffer_DataListAll');
        if (@count($dataAR) > 0) {
            foreach ($dataAR as $index1 => $arrayEntry) {
                if (@count($arrayEntry) > 0) {
                    foreach ($arrayEntry as $indexFileName => $filesAR) {
                        if (@array_key_exists('DDLB', $filesAR) === true) {
                            foreach ($filesAR['DDLB'] as $DDLBentry) {
                                if ((@array_key_exists('ID', $DDLBentry) === true) && (@array_key_exists('OPT', $DDLBentry) === true)) {
                                    if ($DDLBentry['ID'] === $id) {
                                        if ($DebugActive === true) {
                                            $this->SendDebug(__FUNCTION__, 'DDLBentry = ' . $this->DataToString($DDLBentry), 0);
                                        }

                                        return $DDLBentry['OPT'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Mapping failed') . ' // ID = ' . $id . ' // Array = ' . $this->DataToString($dataAR), 0, KL_ERROR);

        return false;
    }


    private function Map_ID_to_TEXT_DataCombined($id)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $dataAR = $this->GetBufferX('MultiBuffer_KWLdataAR_combined');
        if (@count($dataAR) > 0) {
            $keyFound = @array_search($id, array_column($dataAR, 'ID'), true);
            if ($keyFound !== false) {
                if (@array_key_exists('Text', $dataAR[$keyFound]) === true) {
                    $text = $dataAR[$keyFound]['Text'];

                    // If present - remove the sensor number from the beginning of the string
                    preg_match('|^(\d\s)(.*)|', $text, $textMatch);
                    if (@array_key_exists('2', $textMatch) === true) {
                        $text = $textMatch[2];
                    }

                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // Text = ' . $text, 0);
                    }

                    return $text;
                }
            }
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Mapping failed') . ' // ID = ' . $id . ' // Array = ' . $this->DataToString($dataAR), 0, KL_ERROR);

        return false;
    }


    private function Map_ID_to_VA($array, $id)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if (@array_key_exists('ID', $array) === true) {
            $keyFound = @array_search($id, $array['ID'], true);

            if ($keyFound !== false) {
                if (@array_key_exists('VA', $array) === true) {
                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // VA = ' . $array['VA'][$keyFound], 0);
                    }

                    return $array['VA'][$keyFound];
                }
            }
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Mapping failed') . ' // ID = ' . $id . ' // Array = ' . $this->DataToString($array), 0, KL_ERROR);

        return false;
    }


    private function Map_ID_to_VA_DataAll($id)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $AllDataAR = $this->AllData_Get(false);
        if (@count($AllDataAR) > 0) {
            $fileName = $this->Map_ID_to_File($id);
            foreach ($AllDataAR as $index => $AllDataARentry) {
                if (@array_key_exists($fileName, $AllDataARentry) === true) {
                    $array = $AllDataAR[$index][$fileName];
                    if (@array_key_exists('ID', $array) === true) {
                        $result = $this->Map_ID_to_VA($array, $id);

                        if ($DebugActive === true) {
                            $this->SendDebug(__FUNCTION__, 'ID = ' . $id . ' // VA = ' . $result, 0);
                        }

                        return $result;
                    }
                }
            }
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Mapping failed') . ' // ID = ' . $id . ' // Array = ' . $this->DataToString($AllDataAR), 0, KL_ERROR);

        return false;
    }


    private function Map_XMLnr_RefererURL($xmlnr)
    {
        $host = $this->ReadPropertyString('deviceip');

        if ($xmlnr === 0) {
            $refererURL = 'http://' . $host . '/info.htm';
        } elseif ($xmlnr === 1) {
            $refererURL = 'http://' . $host . '/inbetr.htm';
        } elseif ($xmlnr === 3) {  // je nach gewähltem kurzprogramm
            $refererURL = 'http://' . $host . '/party.htm';
            //$refererURL = 'http://' . $host . '/ruhe.htm';
        } elseif ($xmlnr === 4) {
            $refererURL = 'http://' . $host . '/info.htm';
        } elseif ($xmlnr === 5) {
            $refererURL = 'http://' . $host . '/nachheiz.htm';
        } elseif ($xmlnr === 6) {
            $refererURL = 'http://' . $host . '/urlaub.htm';
        } elseif ($xmlnr === 7) {
            $refererURL = 'http://' . $host . '/tinfo.htm';
        } elseif ($xmlnr === 8) {
            $refererURL = 'http://' . $host . '/anzeig.htm';
        } elseif ($xmlnr === 9) {
            $refererURL = 'http://' . $host . '/woche.htm';
        } elseif ($xmlnr === 10) {
            $refererURL = 'http://' . $host . '/web.htm';
        } elseif ($xmlnr === 11) {
            $refererURL = 'http://' . $host . '/syst.htm';
        } elseif ($xmlnr === 12) {
            $refererURL = 'http://' . $host . '/gear.htm';
        } elseif ($xmlnr === 13) {
            $refererURL = 'http://' . $host . '/luft.htm';
        } elseif ($xmlnr === 14) {
            $refererURL = 'http://' . $host . '/fueh.htm';
        } elseif ($xmlnr === 16) {
            $refererURL = 'http://' . $host . '/fehl.htm';
        } else {
            $refererURL = 'http://' . $host . '/info.htm';
        }

        return $refererURL;
    }


    private function MediaObject_Create()
    {
        $result = false;
        if ($this->ReadPropertyBoolean('show_deviceimage') === true) {
            $Name = $this->Translate('Device-Image');
            $Cached = true;
            $Filename = 'Helios_Device_' . $this->InstanceID . $this->System_OrderNumber_Get() . '.png';
            $Ident = 'Device_Image';
            $result_Image_Create = $this->RegisterObjectMedia($Name, $Ident, 1, $this->InstanceID, $Cached, $Filename);
            if ($result_Image_Create === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('The image file could not be created'), 0, KL_ERROR);
                return false;
            }
            $result = true;
        } else {
            $MediaID = @$this->GetIDForIdent('Device_Image');
            if (@IPS_MediaExists($MediaID) === true) {
                $result = @IPS_DeleteMedia($MediaID, true);
            }
        }

        return $result;
    }


    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'SenderID = ' . $SenderID . ' // ' . $this->Translate('Message') . ' = ' . $Message . ' // ' . $this->Translate('Data') . ' = ' . json_encode($Data), 0);
        }

        if ($Message === IPS_KERNELSTARTED) {
            $this->ApplyChanges();
        }

        return true;
    }


    private function Messages_HTML_Generate($dataAR, $TextPre)
    {
        $content = false;
        if (@count($dataAR) > 0) {
            foreach ($dataAR as $dataEntry) {
                if ($dataEntry !== '') {
                    $content = true;
                }
            }
        }

        if (($content === true) && (@count($dataAR) > 0)) {

            $html_table_width = $this->ReadPropertyString('html_table_width');
            $color_background_dec = $this->ReadPropertyInteger('html_color_background');
            $color_title = substr('000000' . dechex($this->ReadPropertyInteger('html_color_title')), -6);
            $color_text = substr('000000' . dechex($this->ReadPropertyInteger('html_color_text')), -6);
            $fontsize_title = $this->ReadPropertyInteger('html_fontsize_title');
            $fontsize_text = $this->ReadPropertyInteger('html_fontsize_text');


            $HTML_CSS_Style = '<style type="text/css">';
            if ($color_background_dec >= 0) {
                $color_background_rgbAR = $this->Convert_ColorDECtoRGB($color_background_dec);
                $color_background_rgbAR[3] = 1; // opacity
            } else {
                $color_background_rgbAR = array(0, 0, 0, 0);
            }
            $HTML_CSS_Style .= '.helios {border-collapse:collapse;border-style:solid;border-width:1px;background-color: rgba(' . $color_background_rgbAR[0] . ',' . $color_background_rgbAR[1] . ',' . $color_background_rgbAR[2] . ', ' . $color_background_rgbAR[3] . ');}
            .helios .th-title' . $this->InstanceID . '{font-family:Arial, sans-serif;font-size:' . $fontsize_title . 'px;font-weight:bold;padding:5px;text-align:center;color:#' . $color_title . ';border-collapse:collapse;border-style:solid;border-width:1px;}
            .helios .td-text' . $this->InstanceID . '{font-family:Arial, sans-serif;font-size:' . $fontsize_text . 'px;font-weight:normal;padding:5px;text-align:center;color:#' . $color_text . ';border-collapse:collapse;border-style:solid;border-width:1px;}
            </style>';

            $HTML = '<html>' . $HTML_CSS_Style;
            $HTML .= '<body><table class="helios"';
            if ($html_table_width !== '') {
                $HTML .= ' style="width:' . $html_table_width . '"';
            }
            $HTML .= '>';

            $TitleAR = array($TextPre . '-Nr', $TextPre . '-Text');
            $HTML .= '<thead><tr><th class="th-title' . $this->InstanceID . '">' . $TitleAR[0] . '</th><th class="th-title' . $this->InstanceID . '">' . $TitleAR[1] . '</th></tr></thead>';
            $HTML .= '<tbody>';
            foreach ($dataAR as $index => $dataARentry) {
                if ($dataARentry !== '') {
                    $Text = $dataARentry;
                    $HTML .= '<tr><td class="td-text' . $this->InstanceID . '">' . $index . '</td><td class="td-text' . $this->InstanceID . '">' . $Text . '</td></tr>';
                }
            }
            $HTML .= '</tbody></table></body></html>';
        } else {
            $HTML = '<html><body>' . $this->Translate('No ') . $this->Translate('Data') . $this->Translate(' available') . '</body></html>';
        }

        return $HTML;
    }


    /**
     * Notification (generates the message and sends it via the selected channel/s)
     *
     * @param $topic
     * @return bool
     */
    private function Notification($topic)
    {
        $notificationStatus = $this->Notification_Helper($topic);
        if (($notificationStatus === 1) || ($notificationStatus === 9)) {
            return false;
        }

        $host = $this->ReadPropertyString('deviceip');
        $type = $this->System_Type_Get();

        $notificationTitle = 'Helios easyControls // ';
        if ($topic === 'filter') {
            $notificationTitle .= 'Filter';
            $notificationText = $this->Translate('Filter change required') . ' // ' . $this->Translate('Days remaining') . ' = ' . $this->Filter_RemainingDays_Get();
            $notificationText .= ' // ' . $this->Translate('Device address') . ' = ' . $host . ' // ' . $this->Translate('Device type') . ' = ' . $type;
        } elseif ($topic === 'errors') {
            $notificationTitle .= $this->Translate('ERROR');
            $notificationText = $this->Translate('Number of error messages in the system') . ' = ' . $this->System_Messages_ErrorCount_Get();
            $notificationText .= ' // ' . $this->Translate('Device address') . ' = ' . $host . ' // ' . $this->Translate('Device type') . ' = ' . $type;
        } elseif ($topic === 'warnings') {
            $notificationTitle .= $this->Translate('WARNING');
            $notificationText = $this->Translate('Number of warning messages in the system') . ' = ' . $this->System_Messages_WarningCount_Get();
            $notificationText .= ' // ' . $this->Translate('Device address') . ' = ' . $host . ' // ' . $this->Translate('Device type') . ' = ' . $type;
        } else {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Unknown topic') . ' - ' . $topic, 0, KL_ERROR);
            return false;
        }


        // Push message
        if ($this->ReadPropertyBoolean('notif_pushmsg_active') === true) {
            $webFrontInstanceID = $this->ReadPropertyInteger('webfrontinstance_id');
            if (($webFrontInstanceID > 0) && (@IPS_InstanceExists($webFrontInstanceID) === true)) {
                if (strlen($notificationText) <= 256) {
                    WFC_PushNotification($webFrontInstanceID, $notificationTitle, $notificationText, '', 0);
                } else {
                    $notificationTextSPLIT = str_split($notificationText, 256);
                    foreach ($notificationTextSPLIT as $notificationTextSplitPart) {
                        WFC_PushNotification($webFrontInstanceID, $notificationTitle, $notificationTextSplitPart, '', 0);
                        IPS_Sleep(250);
                    }
                }
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('WebFront instance ID is invalid or WebFront instance does not exist'), 0, KL_ERROR);
            }
        }

        // Email message
        if ($this->ReadPropertyBoolean('notif_mail_active') === true) {
            $smtpInstanceID = $this->ReadPropertyInteger('smtpinstance_id');
            if (($smtpInstanceID > 0) && (@IPS_InstanceExists($smtpInstanceID) === true)) {
                SMTP_SendMail($smtpInstanceID, $notificationTitle, $notificationText);
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('SMTP instance ID is invalid or SMTP instance does not exist'), 0, KL_ERROR);
            }
        }

        // Own Script
        if ($this->ReadPropertyBoolean('notif_script_active') === true) {
            $scriptID = $this->ReadPropertyInteger('notifscript_id');
            if (($scriptID > 0) && (@IPS_ScriptExists($scriptID) === true)) {
                IPS_RunScriptEx($scriptID, array(
                    'helios_host' => $this->ReadPropertyString('deviceip'),
                    'helios_type' => $this->System_Type_Get(),
                    'helios_topic' => $topic,
                    'helios_title' => $notificationTitle,
                    'helios_text' => $notificationText
                ));
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Script ID is invalid or script does not exist'), 0, KL_ERROR);
            }
        }

        return true;
    }


    /**
     * Notification_Helper ()
     *
     * @param $topic
     * @param $reset
     * @return integer|true
     */
    private function Notification_Helper($topic, $reset = false)
    {
        // Reset notification status?!
        if ($reset === true) {
            if (IPS_GetKernelVersion() >= 5.1) {
                $this->WriteAttributeString('notifsentcheck_' . $topic, '0');
            } else {
                $this->SetBuffer('notifsentcheck_' . $topic, '0');
            }
            return true;
        }

        // Topic not selected in module instance?
        if ($this->ReadPropertyBoolean('notif_sel_' . $topic) === false) {
            return 9;
        }

        // Notification already sent?
        if (IPS_GetKernelVersion() >= 5.1) {
            $check = $this->ReadAttributeString('notifsentcheck_' . $topic);
        } else {
            $check = $this->GetBuffer('notifsentcheck_' . $topic);
        }

        // Notification has already been sent
        if ($check === '1') {
            return 1;
        }

        // Notification has not yet been sent!
        if (IPS_GetKernelVersion() >= 5.1) {
            $this->WriteAttributeString('notifsentcheck_' . $topic, '1');
        } else {
            $this->SetBuffer('notifsentcheck_' . $topic, '1');
        }

        return 0;
    }


    /**
     * Notification_Test (generates a test message and sends it via the activated "channel/s")
     *
     * @return true
     */
    public function Notification_Test()
    {
        $this->Notification('errors');
        return true;
    }


    private function OperatingMode_Cancel($currentMode)
    {
        if ($currentMode === 2) {
            $this->OperatingMode_Party_Set(false, 0, 0);
        }
        if ($currentMode === 3) {
            $this->OperatingMode_Whisper_Set(false, 0, 0);
        }
        if ($currentMode === 4) {
            $vacation_fanlevel = $this->GetValue('OperatingModePresetVacationFanLevel');
            $vacation_dateStartX = $this->GetValue('OperatingModePresetVacationDateStart');
            $vacation_dateEndX = $this->GetValue('OperatingModePresetVacationDateEnd');
            $vacation_dateStart = date('d', $vacation_dateStartX) . '.' . date('m', $vacation_dateStartX) . '.' . date('Y', $vacation_dateStartX);
            $vacation_dateEnd = date('d', $vacation_dateEndX) . '.' . date('m', $vacation_dateEndX) . '.' . date('Y', $vacation_dateEndX);
            $vacation_intervalTime = $this->GetValue('OperatingModePresetVacationIntervalTime'); // wird nur bei auswahl "Intervall" benötigt - trotzdem immer übergeben
            $vacation_activationPeriod = $this->GetValue('OperatingModePresetVacationActivationPeriod'); // wird nur bei auswahl "Intervall" benötigt - trotzdem immer übergeben

            $this->OperatingMode_Vacation_Set(0, $vacation_fanlevel, $vacation_dateStart, $vacation_dateEnd, $vacation_intervalTime, $vacation_activationPeriod);
        }
    }


    private function OperatingMode_GetInternal($varUpdate = true)
    {
        $automanumode = $this->FunctionHelperGET('v00101', __FUNCTION__, true);
        $partymode = $this->FunctionHelperGET('v00094', __FUNCTION__, true);
        $whispermode = $this->FunctionHelperGET('v00099', __FUNCTION__, true);
        $vacationmode = $this->FunctionHelperGET('v00601', __FUNCTION__, true);

        if ($this->ReadPropertyBoolean('debug') === true) {
            $this->SendDebug(__FUNCTION__, 'DEBUG // automanumode = ' . $automanumode . ' // partymode = ' . $this->DataToString($partymode) . ' // whispermode = ' . $this->DataToString($whispermode) . ' // vacationmode = ' . $this->DataToString($vacationmode), 0);
        }

        $modeRemainingTime = 0;

        $result = 0;
        if ($automanumode === '0') {
            $result = 0; // auto
        }
        if ($automanumode === '1') {
            $result = 1; // manu
        }
        if ($partymode === '1') {
            $result = 2; // party
            $modeRemainingTime = $this->FunctionHelperGET('v00093', __FUNCTION__, true);
        }
        if ($whispermode === '1') {
            $result = 3; // whisper
            $modeRemainingTime = $this->FunctionHelperGET('v00098', __FUNCTION__, true);
        }
        if (($vacationmode === '1') || ($vacationmode === '2')) {  // 1 = interval, 2 = constant
            $result = 4; // vacation
        }

        if ($varUpdate === true) {
            $this->SetValue_IfDifferent('OperatingMode', $result);

            if (($modeRemainingTime === 0) || ($modeRemainingTime === false)) {
                $this->SetValue_IfDifferent('OperatingModeRemainingTime', 0);
                IPS_SetHidden($this->GetIDForIdent('OperatingModeRemainingTime'), true);
            } else {
                $this->SetValue_IfDifferent('OperatingModeRemainingTime', $modeRemainingTime);
                IPS_SetHidden($this->GetIDForIdent('OperatingModeRemainingTime'), false);
            }
        }

        return $result;
    }


    public function RequestAction($Ident, $value)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'Ident = ' . $Ident . ' // Value = ' . serialize($value), 0);
        }

        $this->SetValue_IfDifferent($Ident, $value);

        switch ($Ident) {
            case 'ErrorMsgsReset':
                $this->System_Messages_Reset();
                IPS_Sleep(3000);
                $this->SetValue_IfDifferent($Ident, false);
                break;

            case 'FanLevel':
                $this->FanLevel_Set($value);
                break;

            case 'FilterReset':
                $this->Filter_Reset();
                IPS_Sleep(3000);
                $this->SetValue_IfDifferent($Ident, false);
                break;

            case 'FilterChangeInterval':
                $this->Filter_ChangeInterval_Set($value);
                break;

            case 'HumidityControlInternal':
                $this->HumidityControl_Internal_Set($value);
                break;

            case 'OperatingMode':
                if (($value === 0) || ($value === 1)) {
                    $this->OperatingMode_Set($value);
                } elseif ($value === 2) {
                    $this->OperatingMode_Party_SetWithPresets();
                } elseif ($value === 3) {
                    $this->OperatingMode_Whisper_SetWithPresets();
                } elseif ($value === 4) {
                    $this->OperatingMode_Vacation_SetWithPresets();
                }
                break;

            case 'SettingAUTOatMidnight':
                $this->System_AUTOatMidnight_Set($value);
                break;

            case 'SettingCloudSync':
                $this->System_CloudSync_Set($value);
                break;

            case 'SettingModbus':
                $this->System_Modbus_Set($value);
                break;

            case 'SettingSensorControlSleepMode':
                $timeFROM = $this->TimeConvert_Seconds_to_HoursMinutes($this->GetValue('SettingSensorControlSleepModeFrom'));
                $timeTO = $this->TimeConvert_Seconds_to_HoursMinutes($this->GetValue('SettingSensorControlSleepModeTo'));
                $this->System_SensorControlSleepMode_Set($value, $timeFROM, $timeTO);
                break;

            case 'SettingSensorControlSleepModeFrom':
                $this->System_SensorControlSleepModeFROM_Set($this->TimeConvert_Seconds_to_HoursMinutes($value));
                break;

            case 'SettingSensorControlSleepModeTo':
                $this->System_SensorControlSleepModeTO_Set($this->TimeConvert_Seconds_to_HoursMinutes($value));
                break;

            case 'SettingSoftwareUpdateAutomatic':
                $this->System_SoftwareUpdateAutomatic_Set($value);
                break;

            case 'WeekProgram':
                $this->WeekProgram_Set($value);
                break;

            default:
                // no extra action - only change value of variable
                break;
        }

        return true;
    }


    /**
     * Search_DataAR (xxxxxxxxxx)
     *
     * @param $search
     * @param $searchindex
     * @return array|false
     */
    private function Search_DataAR($search, $searchindex)
    {
        $array = $this->GetBufferX('MultiBuffer_KWLdataAR_combined');

        $keyFound = @array_search($search, array_column($array, $searchindex), true);

        if ($keyFound !== false) {
            return $array[$keyFound];
        }

        return false;
    }


    private function TimeConvert_HoursMinutes_to_Seconds($timeString)
    {
        return strtotime('1970/01/01 ' . $timeString);
    }


    private function TimeConvert_Seconds_to_HoursMinutes($seconds)
    {
        return date('H:i', $seconds);
    }


    /**
     * Timer_Control (stop timer, start timer in the set interval, handle timer call and call corresponding function)
     *
     * @param string $TimerName
     * @param int $option
     * @return bool
     */
    public function Timer_Control(string $TimerName, int $option)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');
        $IntervalSeconds = 0;

        if ($option === 0) {
            $result = $this->SetTimerInterval($TimerName, 0);
        } elseif ($option === 1) {
            if (IPS_GetKernelRunlevel() === KR_READY) {
                if ($TimerName === 'Update_BasicInfo') {
                    $IntervalSeconds = $this->Timer_Control_IntervalToTime(3, 17, 37);
                    if ($IntervalSeconds === false) {
                        return false;
                    }
                } elseif ($TimerName === 'Update_Data') {
                    $IntervalSeconds = $this->ReadPropertyInteger('timerinterval');
                } elseif ($TimerName === 'FanLevel_Period') {
                    $IntervalSeconds = $this->GetBuffer('FanLevel_Period_Seconds');
                }

                if ($IntervalSeconds === 0) {
                    $option = 0;
                }

                $result = $this->SetTimerInterval($TimerName, $IntervalSeconds * 1000);
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('Kernel is not ready! Kernel Runlevel = ') . IPS_GetKernelRunlevel(), 0);
                return false;
            }
        } elseif ($option === 2) {
            if ($TimerName === 'Update_BasicInfo') {
                $this->BasicInit();
                $result = true;
            } elseif ($TimerName === 'Update_Data') {
                $this->Update_Data();
                $result = true;
            } elseif ($TimerName === 'FanLevel_Period') {
                $this->OperatingMode_Set('auto');
                $this->Timer_Control('FanLevel_Period', 0);
                $result = true;
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR // Unknown timer') . ' // Name = ' . $TimerName, 0, KL_ERROR);
                $result = false;
            }
        } else {
            $result = $this->SetTimerInterval($TimerName, 0);
        }

        if ($DebugActive === true) {
            if ($option === 0) {
                $this->SendDebug(__FUNCTION__, "Timer '" . $TimerName . "' Option '" . $option . "' // " . $this->Translate('Timer has been stopped'), 0);
            } elseif ($option === 1) {
                $this->SendDebug(__FUNCTION__, "Timer '" . $TimerName . "' Option '" . $option . "' // " . $this->Translate('Timer has been started') . ' // ' . $this->Translate('Interval') . ': ' . $IntervalSeconds . ' ' . $this->Translate('seconds'), 0);
            } elseif ($option === 2) {
                $this->SendDebug(__FUNCTION__, "Timer '" . $TimerName . "' Option '" . $option . "' // " . $this->Translate('Functions were called'), 0);
            } else {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . " // Option '" . $option . "' " . $this->Translate('is invalid') . " // Timer '" . $TimerName . "' // " . $this->Translate('Timer has been stopped'), 0, KL_ERROR);
            }
        }

        return $result;
    }


    private function Timer_Control_IntervalToTime($hour, $minute, $second = false)
    {
        try {
            $timeSpecific = new DateTime();
        } catch (Exception $e) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0013 // ' . $this->Translate('DateTime object could not be created - timer is stopped'), 0, KL_ERROR);
            return false;
        }

        if ($second !== false) {
            $timeSpecific->setTime($hour, $minute, $second);
        } else {
            $timeSpecific->setTime($hour, $minute, mt_rand(13, 43));
        }

        $timeNow = time();
        if ($timeNow >= $timeSpecific->getTimestamp()) {
            $timeSpecific->modify('+1 day');
        }

        $timeDiff_Now_Specific = $timeSpecific->getTimestamp() - $timeNow;

        return $timeDiff_Now_Specific * 1000;
    }


    private function Variables_Create()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        // variables for display information/states
        if ($this->ReadPropertyBoolean('show_devicecomponentinformation') === true) {
            if ($this->Preheater_Status_Get() === true) {
                $this->Variable_Register('OperatingHoursPreheater', $this->Translate('Operating hours') . ' - ' . $this->Translate('Preheater'), 'HELIOS.OperatingHours', '', 1, false);
            } else {
                $this->Variable_Unregister('OperatingHoursPreheater');
            }
            if ($this->Afterheater_Status_Get() === true) {
                $this->Variable_Register('OperatingHoursAfterheater', $this->Translate('Operating hours') . ' - ' . $this->Translate('Afterheater'), 'HELIOS.OperatingHours', '', 1, false);
            } else {
                $this->Variable_Unregister('OperatingHoursAfterheater');
            }
            $this->Variable_Register('OperatingHoursExtractAirFan', $this->Translate('Operating hours') . ' - ' . $this->Translate('Extract fan'), 'HELIOS.OperatingHours', '', 1, false);
            $this->Variable_Register('OperatingHoursSupplyAirFan', $this->Translate('Operating hours') . ' - ' . $this->Translate('Supply air fan'), 'HELIOS.OperatingHours', '', 1, false);
        } else {
            $this->Variable_Unregister('OperatingHoursPreheater');
            $this->Variable_Unregister('OperatingHoursAfterheater');
            $this->Variable_Unregister('OperatingHoursExtractAirFan');
            $this->Variable_Unregister('OperatingHoursSupplyAirFan');
        }
        if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
            $this->Variable_Register('DeviceType', $this->Translate('Device-Type'), '', 'Information', 3, false);
            $this->Variable_Register('OperatingHours', $this->Translate('Operating hours') . ' - System', 'HELIOS.OperatingHours', '', 1, false);
            $this->Variable_Register('ProductionCode', $this->Translate('Production-Code'), '', 'Information', 3, false);
            $this->Variable_Register('SecurityNumber', $this->Translate('Security-Number'), '', 'Information', 3, false);
            $this->Variable_Register('SerialNumber', $this->Translate('Serial-Number'), '', 'Information', 3, false);
            $this->Variable_Register('SoftwareVersion', $this->Translate('Software-Version'), '', 'Information', 2, false);
            $this->Variable_Register('SystemMsgsHTMLDataExchange', $this->Translate('System - Messages (Data exchange)'), '~HTMLBox', 'Information', 3, false);
            $this->Variable_Register('SystemMsgsHTMLError', $this->Translate('System - Messages (Error)'), '~HTMLBox', 'Information', 3, false);
            $this->Variable_Register('SystemMsgsHTMLInfo', $this->Translate('System - Messages (Info)'), '~HTMLBox', 'Information', 3, false);
            $this->Variable_Register('SystemMsgsHTMLStatus', $this->Translate('System - Messages (Status)'), '~HTMLBox', 'Information', 3, false);
            $this->Variable_Register('SystemMsgsHTMLWarning', $this->Translate('System - Messages (Warnings)'), '~HTMLBox', 'Information', 3, false);
        } else {
            $this->Variable_Unregister('DeviceType');
            $this->Variable_Unregister('OperatingHours');
            $this->Variable_Unregister('ProductionCode');
            $this->Variable_Unregister('SecurityNumber');
            $this->Variable_Unregister('SerialNumber');
            $this->Variable_Unregister('SoftwareVersion');
            $this->Variable_Unregister('SystemMsgsHTMLDataExchange');
            $this->Variable_Unregister('SystemMsgsHTMLError');
            $this->Variable_Unregister('SystemMsgsHTMLInfo');
            $this->Variable_Unregister('SystemMsgsHTMLStatus');
            $this->Variable_Unregister('SystemMsgsHTMLWarning');
        }
        $this->Variable_Register('Bypass', $this->Translate('Bypass'), 'HELIOS.Bypass', '', 0, false);
        $this->Variable_Register('CO2Control', $this->Translate('CO2-Control'), 'HELIOS.VOCCO2HUMControl', '', 1, false);
        $this->Variable_Register('DefrostStateHeatExchanger', $this->Translate('Defrost - Heat exchanger'), 'HELIOS.DefrostState', '', 0, false);
        $this->Variable_Register('DefrostStateHotWaterRegister', $this->Translate('Defrost - Hot water register'), 'HELIOS.DefrostState', '', 0, false);
        $this->Variable_Register('FanLevelPercent', $this->Translate('Fan level') . ' (%)', 'HELIOS.FanLevelPercent', '', 1, false);
        $this->Variable_Register('FilterRemainingTime', $this->Translate('Filter - Remaining time'), 'HELIOS.Days', '', 1, false);
        $this->Variable_Register('FilterRemainingTimeBOOL', $this->Translate('Filter - Change required'), 'HELIOS.ErrorNoYes', '', 0, false);
        $this->Variable_Register('HeatRecoveryEfficiency', $this->Translate('Heat recovery efficiency'), 'HELIOS.HeatRecoveryEfficiency', '', 1, false);
        $this->Variable_Register('HumidityControl', $this->Translate('Humidity-Control'), 'HELIOS.VOCCO2HUMControl', '', 1, false);
        $this->Variable_Register('OperatingModeRemainingTime', $this->Translate('Operating mode - Remaining time'), 'HELIOS.OperatingModeRemainingTime', '', 1, false);
        $this->Variable_Register('AfterheaterState', $this->Translate('Afterheater'), 'HELIOS.PreAfterheaterState', '', 0, false);
        $this->Variable_Register('PreheaterState', $this->Translate('Preheater'), 'HELIOS.PreAfterheaterState', '', 0, false);
        $this->Variable_Register('SystemError', $this->Translate('System - Error'), 'HELIOS.ErrorNoYes', '', 0, false);
        $this->Variable_Register('SystemErrorCount', $this->Translate('System - Error (count)'), '', 'Information', 1, false);
        $this->Variable_Register('SystemInfo', $this->Translate('System - Info'), 'HELIOS.ErrorNoYes', '', 0, false);
        $this->Variable_Register('SystemInfoCount', $this->Translate('System - Info (count)'), '', 'Information', 1, false);
        $this->Variable_Register('SystemWarning', $this->Translate('System - Warning'), 'HELIOS.ErrorNoYes', '', 0, false);
        $this->Variable_Register('SystemWarningCount', $this->Translate('System - Warning (count)'), '', 'Information', 1, false);
        $this->Variable_Register('VOCControl', $this->Translate('VOC-Control'), 'HELIOS.VOCCO2HUMControl', '', 1, false);

        // variables for control device
        if ($this->ReadPropertyBoolean('show_devicesystemsettings') === true) {
            $this->Variable_Register('SettingSensorControlSleepMode', $this->Translate('Setting') . ' - ' . $this->Translate('Sleep mode for sensor control'), 'HELIOS.StateSwitch', '', 0, true);
            $this->Variable_Register('SettingSensorControlSleepModeFrom', $this->Translate('Setting') . ' - ' . $this->Translate('Sleep mode for sensor control - From'), '~UnixTimestampTime', 'Clock', 1, true);
            $this->Variable_Register('SettingSensorControlSleepModeTo', $this->Translate('Setting') . ' - ' . $this->Translate('Sleep mode for sensor control - To'), '~UnixTimestampTime', 'Clock', 1, true);
            $this->Variable_Register('SettingAUTOatMidnight', $this->Translate('Setting') . ' - ' . $this->Translate("AUTO at 0 o'clock"), 'HELIOS.StateSwitch', '', 0, true);
            $this->Variable_Register('SettingCloudSync', $this->Translate('Setting') . ' - ' . $this->Translate('Cloud sync'), 'HELIOS.StateSwitch', '', 0, true);
            $this->Variable_Register('SettingModbus', $this->Translate('Setting') . ' - Modbus', 'HELIOS.StateSwitch', '', 0, true);
            $this->Variable_Register('SettingSoftwareUpdateAutomatic', $this->Translate('Setting') . ' - ' . $this->Translate('Software update automatic'), 'HELIOS.StateSwitch', '', 0, true);
        } else {
            $this->Variable_Unregister('SettingSensorControlSleepMode');
            $this->Variable_Unregister('SettingSensorControlSleepModeFrom');
            $this->Variable_Unregister('SettingSensorControlSleepModeTo');
            $this->Variable_Unregister('SettingAUTOatMidnight');
            $this->Variable_Unregister('SettingCloudSync');
            $this->Variable_Unregister('SettingModbus');
            $this->Variable_Unregister('SettingSoftwareUpdateAutomatic');
        }
        if ($this->FunctionHelperGET('v02152', __FUNCTION__) === '1') {
            $this->Variable_Register('HumidityControlInternal', $this->Translate('Humidity-Control (internal)'), 'HELIOS.HumidityControlInternal', '', 0, true);
            $this->Variable_Register('HumidityControlInternalExtractAir', $this->Translate('Humidity-Control (internal - extract air)'), 'HELIOS.Intensity0100', '', 1, false);
        } else {
            $this->Variable_Unregister('HumidityControlInternal');
            $this->Variable_Unregister('HumidityControlInternalExtractAir');
        }
        $this->Variable_Register('ErrorMsgsReset', $this->Translate('Error messages - Reset'), 'HELIOS.ResetAction', '', 0, true);
        $this->Variable_Register('FanLevel', $this->Translate('Fan level'), 'HELIOS.FanLevel', '', 1, true);
        $this->Variable_Register('FilterChangeInterval', $this->Translate('Filter - Change interval'), 'HELIOS.Filter.Months', '', 1, true);
        $this->Variable_Register('FilterReset', $this->Translate('Filter - Reset'), 'HELIOS.ResetAction', '', 0, true);
        $this->Variable_Register('OperatingMode', $this->Translate('Operating mode'), 'HELIOS.OperatingMode', '', 1, true);
        $this->Variable_Register('OperatingModePresetPartyDuration', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Party') . ' - ' . $this->Translate('Duration'), 'HELIOS.ModeDuration', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetPartyDuration', 'Attr_OperatingModePresetPartyDuration');
        $this->Variable_Register('OperatingModePresetPartyFanLevel', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Party') . ' - ' . $this->Translate('Fan level'), 'HELIOS.FanLevel', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetPartyFanLevel', 'Attr_OperatingModePresetPartyFanLevel');
        $this->Variable_Register('OperatingModePresetWhisperDuration', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Whisper') . ' - ' . $this->Translate('Duration'), 'HELIOS.ModeDuration', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetWhisperDuration', 'Attr_OperatingModePresetWhisperDuration');
        $this->Variable_Register('OperatingModePresetWhisperFanLevel', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Whisper') . ' - ' . $this->Translate('Fan level'), 'HELIOS.FanLevel', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetWhisperFanLevel', 'Attr_OperatingModePresetWhisperFanLevel');
        $this->Variable_Register('OperatingModePresetVacationProgram', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('Program'), 'HELIOS.ModeVacationProgram', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationProgram', 'Attr_OperatingModePresetVacationProgram');
        $this->Variable_Register('OperatingModePresetVacationFanLevel', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('Fan level'), 'HELIOS.FanLevel', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationFanLevel', 'Attr_OperatingModePresetVacationFanLevel');
        $this->Variable_Register('OperatingModePresetVacationDateStart', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('Date start'), '~UnixTimestampDate', 'Calendar', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationDateStart', 'Attr_OperatingModePresetVacationDateStart');
        $this->Variable_Register('OperatingModePresetVacationDateEnd', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('Date end'), '~UnixTimestampDate', 'Calendar', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationDateEnd', 'Attr_OperatingModePresetVacationDateEnd');
        $this->Variable_Register('OperatingModePresetVacationIntervalTime', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('Interval time'), 'HELIOS.ModeIntervalTime', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationIntervalTime', 'Attr_OperatingModePresetVacationIntervalTime');
        $this->Variable_Register('OperatingModePresetVacationActivationPeriod', $this->Translate('Operating mode preset') . ' - ' . $this->Translate('Vacation') . ' - ' . $this->Translate('On time'), 'HELIOS.ModeActivationPeriod', '', 1, true);
        $this->SetValue_ToDefaultOnce('OperatingModePresetVacationActivationPeriod', 'Attr_OperatingModePresetVacationActivationPeriod');
        $this->Variable_Register('WeekProgram', $this->Translate('Week program'), 'HELIOS.WeekProgram', '', 1, true);

        // variables for temperature sensors
        if ($this->FeatureCheck('v00104') === true) {
            $this->Variable_Register('TemperatureOutdoorAir', $this->Translate('Temperature (Outdoor air)'), 'HELIOS.Temperature.Outdoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureOutdoorAir');
        }
        if ($this->FeatureCheck('v00105') === true) {
            $this->Variable_Register('TemperatureSupplyAir', $this->Translate('Temperature (Supply air)'), 'HELIOS.Temperature.Indoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureSupplyAir');
        }
        if ($this->FeatureCheck('v00107') === true) {
            $this->Variable_Register('TemperatureExtractAir', $this->Translate('Temperature (Extract air)'), 'HELIOS.Temperature.Indoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureExtractAir');
        }
        if ($this->FeatureCheck('v00106') === true) {
            $this->Variable_Register('TemperatureExhaustAir', $this->Translate('Temperature (Exhaust air)'), 'HELIOS.Temperature.Indoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureExhaustAir');
        }
        if ($this->FeatureCheck('v00108') === true) {
            $this->Variable_Register('TemperatureDuctOutdoorAir', $this->Translate('Temperature (Outdoor air duct)'), 'HELIOS.Temperature.Outdoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureDuctOutdoorAir');
        }
        if ($this->FeatureCheck('v00146') === true) {
            $this->Variable_Register('TemperatureDuctSupplyAir', $this->Translate('Temperature (Supply air duct)'), 'HELIOS.Temperature.Indoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureDuctSupplyAir');
        }
        if ($this->FeatureCheck('v00110') === true) {
            $this->Variable_Register('TemperatureReturnWWRegister', $this->Translate('Temperature (Return WW-Register)'), 'HELIOS.Temperature.Indoor', '', 2, false);
        } else {
            $this->Variable_Unregister('TemperatureReturnWWRegister');
        }

        // variables for CO2 sensors
        $sid = 1;
        $textid = 1081;
        for ($i = 128; $i <= 135; $i++) {
            if ($this->FeatureCheck('v00' . $i) === true) {
                $sensorText = @urldecode($this->Map_ID_to_VA_DataAll('v0' . $textid));
                $this->Variable_Register('SensorCO2_' . $sid, $this->Translate('CO2 sensor') . ' (' . $sensorText . ')', 'HELIOS.CO2VOC.ppm', '', 1, false);
            } else {
                $this->Variable_Unregister('SensorCO2_' . $sid);
            }
            $sid++;
            $textid++;
        }

        // variables for humidity sensors
        $sid = 1;
        $textid = 1071;
        for ($i = 111; $i <= 118; $i++) {
            if ($this->FeatureCheck('v00' . $i) === true) {
                $sensorText = @urldecode($this->Map_ID_to_VA_DataAll('v0' . $textid));
                $this->Variable_Register('SensorHumidityRH_' . $sid, $this->Translate('Humidity sensor') . ' (' . $sensorText . ')', 'HELIOS.RelativeHumidity', '', 1, false);
                $this->Variable_Register('SensorHumidityTC_' . $sid, $this->Translate('Humidity sensor') . ' (' . $sensorText . ')', 'HELIOS.Temperature.Indoor', '', 2, false);
            } else {
                $this->Variable_Unregister('SensorHumidityRH_' . $sid);
                $this->Variable_Unregister('SensorHumidityTC_' . $sid);
            }
            $sid++;
            $textid++;
        }

        // variables for VOC sensors
        $sid = 1;
        $textid = 1091;
        for ($i = 136; $i <= 143; $i++) {
            if ($this->FeatureCheck('v00' . $i) === true) {
                $sensorText = @urldecode($this->Map_ID_to_VA_DataAll('v0' . $textid));
                $this->Variable_Register('SensorVOC_' . $sid, $this->Translate('VOC sensor') . ' (' . $sensorText . ')', 'HELIOS.CO2VOC.ppm', '', 1, false);
            } else {
                $this->Variable_Unregister('SensorVOC_' . $sid);
            }
            $sid++;
            $textid++;
        }

        // variables for other sensors
        if ($this->FeatureCheck('v00348') === true) {
            $this->Variable_Register('FanSpeedSupplyAir', $this->Translate('Fan speed (supply air)'), 'HELIOS.FanSpeedRPM', '', 1, false);
        } else {
            $this->Variable_Unregister('FanSpeedSupplyAir');
        }
        if ($this->FeatureCheck('v00349') === true) {
            $this->Variable_Register('FanSpeedExtractAir', $this->Translate('Fan speed (extract air)'), 'HELIOS.FanSpeedRPM', '', 1, false);
        } else {
            $this->Variable_Unregister('FanSpeedExtractAir');
        }

        // variables for pre/afterheater values
        if ($this->Preheater_Status_Get() === true) {
            $this->Variable_Register('PreheaterActualPower', $this->Translate('Preheater - Actual power'), 'HELIOS.PreAfterheater.Perc.Int', '', 1, false);
            $this->Variable_Register('PreheaterHeatOutputDelivered', $this->Translate('Preheater - Heat output delivered'), 'HELIOS.PreAfterheater.Perc.Float', '', 2, false);
        } else {
            $this->Variable_Unregister('PreheaterActualPower');
            $this->Variable_Unregister('PreheaterHeatOutputDelivered');
        }
        if ($this->Afterheater_Status_Get() === true) {
            $this->Variable_Register('AfterheaterActualPower', $this->Translate('Afterheater - Actual power'), 'HELIOS.PreAfterheater.Perc.Int', '', 1, false);
            $this->Variable_Register('AfterheaterHeatOutputDelivered', $this->Translate('Afterheater - Heat output delivered'), 'HELIOS.PreAfterheater.Perc.Float', '', 2, false);
        } else {
            $this->Variable_Unregister('AfterheaterActualPower');
            $this->Variable_Unregister('AfterheaterHeatOutputDelivered');
        }

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, $this->Translate('The variables were successfully created'), 0);
        }

        return true;
    }


    private function VariableProfiles_Create($overwrite = false)
    {
        if ($overwrite === true) {
            $VarProfilesAR = array('HELIOS.Bypass', 'HELIOS.CO2VOC.ppm', 'HELIOS.Days', 'HELIOS.DefrostState', 'HELIOS.ErrorNoYes', 'HELIOS.FanLevel', 'HELIOS.FanSpeedRPM', 'HELIOS.FanLevelPercent', 'HELIOS.Filter.Months', 'HELIOS.HeatRecoveryEfficiency', 'HELIOS.Mode', 'HELIOS.ModeDuration', 'HELIOS.ModeIntervalTime', 'HELIOS.ModeActivationPeriod', 'HELIOS.OperatingModeRemainingTime', 'HELIOS.ModeVacationProgram', 'HELIOS.OperatingHours', 'HELIOS.OperatingMode', 'HELIOS.PreAfterheater.Perc.Float', 'HELIOS.PreAfterheater.Perc.Int', 'HELIOS.PreAfterheaterState', 'HELIOS.RelativeHumidity', 'HELIOS.ResetAction', 'HELIOS.StateSwitch', 'HELIOS.Temperature.Indoor', 'HELIOS.Temperature.Outdoor', 'HELIOS.VOCCO2HUMControl', 'HELIOS.WeekProgram');
            foreach ($VarProfilesAR as $VarProfilNameDEL) {
                @IPS_DeleteVariableProfile($VarProfilNameDEL);
            }
        }

        $VarProfileName = 'HELIOS.Bypass';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Closed'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Open'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Link');
        }

        $VarProfileName = 'HELIOS.CO2VOC.ppm';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 3500, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' ppm');
            IPS_SetVariableProfileAssociation($VarProfileName, 0, '%d', '', 0x00FF00);
            IPS_SetVariableProfileAssociation($VarProfileName, 800, '%d', '', 0xFFFF00);
            IPS_SetVariableProfileAssociation($VarProfileName, 1000, '%d', '', 0xFF7F00);
            IPS_SetVariableProfileAssociation($VarProfileName, 1400, '%d', '', 0xFF0000);
            IPS_SetVariableProfileIcon($VarProfileName, 'Fog');
        }

        $VarProfileName = 'HELIOS.Days';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, '%d ' . $this->Translate('days'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, '%d ' . $this->Translate('day'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, '%d ' . $this->Translate('days'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Calendar');
        }

        $VarProfileName = 'HELIOS.DefrostState';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Inactive'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Active'), '', 0x00FF00);
            IPS_SetVariableProfileIcon($VarProfileName, 'Snowflake');
        }

        $VarProfileName = 'HELIOS.ErrorNoYes';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('No'), 'Ok', 0x00FF00);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Yes'), 'Warning', 0xFF0000);
            IPS_SetVariableProfileIcon($VarProfileName, 'Information');
        }

        $VarProfileName = 'HELIOS.FanLevel';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            $FanLevelRangeAR = $this->FanLevel_Range_Determine();
            if ((@array_key_exists('min', $FanLevelRangeAR) === true) && (@array_key_exists('max', $FanLevelRangeAR) === true)) {
                $FanLevelMIN = $FanLevelRangeAR['min'];
                $FanLevelMAX = $FanLevelRangeAR['max'];
            } else {
                $FanLevelMIN = 0;
                $FanLevelMAX = 4;
            }

            IPS_CreateVariableProfile($VarProfileName, 1);
            for ($flc = $FanLevelMIN; $flc <= $FanLevelMAX; $flc++) {
                if ($flc === 0) {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, $flc . ' (' . $this->Translate('Off') . ')', '', -1);
                } elseif ($flc === 1) {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, $flc . ' (' . $this->Translate('Moisture protection') . ')', '', -1);
                } elseif ($flc === 2) {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, $flc . ' (' . $this->Translate('Reduced ventilation') . ')', '', -1);
                } elseif ($flc === 3) {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, $flc . ' (' . $this->Translate('Nominal ventilation') . ')', '', -1);
                } elseif ($flc === 4) {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, $flc . ' (' . $this->Translate('Intensive ventilation') . ')', '', -1);
                } else {
                    IPS_SetVariableProfileAssociation($VarProfileName, $flc, (string)$flc, '', -1);
                }
            }
            IPS_SetVariableProfileIcon($VarProfileName, 'Ventilation');
        }

        $VarProfileName = 'HELIOS.FanSpeedRPM';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 3000, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' rpm');
            IPS_SetVariableProfileIcon($VarProfileName, 'Ventilation');
        }

        $VarProfileName = 'HELIOS.FanLevelPercent';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 100, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' %');
            IPS_SetVariableProfileIcon($VarProfileName, 'Intensity');
        }

        $VarProfileName = 'HELIOS.Filter.Months';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 3, 12, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' ' . $this->Translate('months'));
            IPS_SetVariableProfileIcon($VarProfileName, 'Calendar');
        }

        $VarProfileName = 'HELIOS.HeatRecoveryEfficiency';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileDigits($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 150, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' %');
            IPS_SetVariableProfileIcon($VarProfileName, 'Information');
        }

        $VarProfileName = 'HELIOS.HumidityControlInternal';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Off'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Stepless'), '', 0x00FF00);
            IPS_SetVariableProfileIcon($VarProfileName, 'Power');
        }

        $VarProfileName = 'HELIOS.Intensity0100';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 100, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' %');
            IPS_SetVariableProfileIcon($VarProfileName, 'Intensity');
        }

        $VarProfileName = 'HELIOS.ModeDuration';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 5, 180, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' ' . $this->Translate('minutes'));
            IPS_SetVariableProfileIcon($VarProfileName, 'Clock');
        }

        $VarProfileName = 'HELIOS.ModeIntervalTime';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 1, 24, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, '%d ' . $this->Translate('hours'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, '%d ' . $this->Translate('hour'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, '%d ' . $this->Translate('hours'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Clock');
        }

        $VarProfileName = 'HELIOS.ModeActivationPeriod';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 5, 300, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' ' . $this->Translate('minutes'));
            IPS_SetVariableProfileIcon($VarProfileName, 'Clock');
        }

        $VarProfileName = 'HELIOS.OperatingModeRemainingTime';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 180, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, '%d ' . $this->Translate('minutes'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, '%d ' . $this->Translate('minute'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, '%d ' . $this->Translate('minutes'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Clock');
        }

        $VarProfileName = 'HELIOS.ModeVacationProgram';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Interval'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Constant'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Database');
        }

        $VarProfileName = 'HELIOS.OperatingHours';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 175200, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, '%d ' . $this->Translate('hours'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, '%d ' . $this->Translate('hour'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, '%d ' . $this->Translate('hours'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Clock');
        }

        $VarProfileName = 'HELIOS.OperatingMode';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Auto'), 'Climate', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Manual'), 'Execute', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, $this->Translate('Party mode'), 'Party', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 3, $this->Translate('Whisper mode'), 'Sleep', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 4, $this->Translate('Vacation mode'), 'Wellness', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Information');
        }

        $VarProfileName = 'HELIOS.PreAfterheater.Perc.Float';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 2);
            IPS_SetVariableProfileDigits($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 100, 0.1);
            IPS_SetVariableProfileText($VarProfileName, '', $this->Translate(' %'));
            IPS_SetVariableProfileIcon($VarProfileName, 'Radiator');
        }

        $VarProfileName = 'HELIOS.PreAfterheater.Perc.Int';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 100, 1);
            IPS_SetVariableProfileText($VarProfileName, '', ' %');
            IPS_SetVariableProfileIcon($VarProfileName, 'Radiator');
        }

        $VarProfileName = 'HELIOS.PreAfterheaterState';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Deactivated'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Ready'), '', 0x00FF00);
            IPS_SetVariableProfileIcon($VarProfileName, 'Radiator');
        }

        $VarProfileName = 'HELIOS.RelativeHumidity';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 100, 1);
            IPS_SetVariableProfileText($VarProfileName, '', $this->Translate('% rH'));
            IPS_SetVariableProfileIcon($VarProfileName, 'Drops');
        }

        $VarProfileName = 'HELIOS.ResetAction';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('>>'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Reset'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Execute');
        }

        $VarProfileName = 'HELIOS.StateSwitch';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 0);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Off'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('On'), '', 0x00FF00);
            IPS_SetVariableProfileIcon($VarProfileName, 'Power');
        }

        $VarProfileName = 'HELIOS.Temperature.Indoor';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 2);
            IPS_SetVariableProfileDigits($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, 0, 65, 0.1);
            IPS_SetVariableProfileText($VarProfileName, '', ' °C');
            IPS_SetVariableProfileIcon($VarProfileName, 'Temperature');
        }

        $VarProfileName = 'HELIOS.Temperature.Outdoor';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 2);
            IPS_SetVariableProfileDigits($VarProfileName, 1);
            IPS_SetVariableProfileValues($VarProfileName, -30, 65, 0.1);
            IPS_SetVariableProfileText($VarProfileName, '', ' °C');
            IPS_SetVariableProfileIcon($VarProfileName, 'Temperature');
        }

        $VarProfileName = 'HELIOS.VOCCO2HUMControl';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->Translate('Off'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->Translate('Stepped'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, $this->Translate('Stepless'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Information');
        }

        $VarProfileName = 'HELIOS.WeekProgram';
        if (IPS_VariableProfileExists($VarProfileName) === false) {
            IPS_CreateVariableProfile($VarProfileName, 1);
            IPS_SetVariableProfileAssociation($VarProfileName, 0, $this->ReadPropertyString('weekprogram_0_name'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 1, $this->ReadPropertyString('weekprogram_1_name'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 2, $this->ReadPropertyString('weekprogram_2_name'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 3, $this->ReadPropertyString('weekprogram_3_name'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 4, $this->ReadPropertyString('weekprogram_4_name'), '', -1);
            IPS_SetVariableProfileAssociation($VarProfileName, 5, $this->Translate('Off'), '', -1);
            IPS_SetVariableProfileIcon($VarProfileName, 'Database');
        }
    }


    private function WeeklyPlan_Create()
    {
        $eventID = @IPS_GetObjectIDByIdent('WeeklyPlan', $this->InstanceID);
        if ($eventID === false) {
            $eventID = IPS_CreateEvent(2);
            IPS_SetParent($eventID, $this->InstanceID);
            IPS_SetName($eventID, $this->Translate('Weekly plan (Fan level)'));
            IPS_SetIdent($eventID, 'WeeklyPlan');
            IPS_SetIcon($eventID, 'Calendar');
            IPS_SetEventScheduleGroup($eventID, 0, 1);
            IPS_SetEventScheduleGroup($eventID, 1, 2);
            IPS_SetEventScheduleGroup($eventID, 2, 4);
            IPS_SetEventScheduleGroup($eventID, 3, 8);
            IPS_SetEventScheduleGroup($eventID, 4, 16);
            IPS_SetEventScheduleGroup($eventID, 5, 32);
            IPS_SetEventScheduleGroup($eventID, 6, 64);
            $FanLevel_MIN = $this->System_FanLevelMin_Get();
            if (($FanLevel_MIN === 0) || ($FanLevel_MIN === NULL)) {
                IPS_SetEventScheduleAction($eventID, 0, '0 - ' . $this->Translate('Off'), 0xEC5A1F, 'HELIOS_FanLevel_Set($_IPS["TARGET"], 0);');
            }
            IPS_SetEventScheduleAction($eventID, 1, '1 - ' . $this->Translate('Moisture protection'), 0x1BE2EB, 'HELIOS_FanLevel_Set($_IPS["TARGET"], 1);');
            IPS_SetEventScheduleAction($eventID, 2, '2 - ' . $this->Translate('Reduced ventilation'), 0x198F775, 'HELIOS_FanLevel_Set($_IPS["TARGET"], 2);');
            IPS_SetEventScheduleAction($eventID, 3, '3 - ' . $this->Translate('Nominal ventilation'), 0x19CE0D, 'HELIOS_FanLevel_Set($_IPS["TARGET"], 3);');
            IPS_SetEventScheduleAction($eventID, 4, '4 - ' . $this->Translate('Intensive ventilation'), 0xE5C012, 'HELIOS_FanLevel_Set($_IPS["TARGET"], 4);');
            IPS_SetEventActive($eventID, false);
        }

        return true;
    }


    /*** FUNCTIONS FOR PUBLIC USE ***************************************************************************/

    public function Afterheater_ActualPower_Get()
    {
        $result = $this->FunctionHelperGET('v02118', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('AfterheaterActualPower', $result);
        }

        return $result;
    }


    public function Afterheater_HeatOutputDelivered_Get()
    {
        $result1 = $this->FunctionHelperGET('v01106', __FUNCTION__, true);
        $result2 = $this->FunctionHelperGET('v01109', __FUNCTION__, true);

        $result = NULL;
        if (($result1 !== NULL) && ($result2 !== NULL)) {
            if ((float)$result1 === 0) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0029 // ' . $this->Translate('Implausible data - abort') . ' // result1 = ' . $result1 . ' // result2 = ' . $result2, 0, KL_ERROR);
                return false;
            }

            if ((float)$result2 === 0) {
                $result = 0;
            } else {
                $result = round(((float)$result2 / (float)$result1) * 100, 1);
            }

            $this->SetValue_IfDifferent('AfterheaterHeatOutputDelivered', $result);
        }

        return $result;
    }


    public function Afterheater_Status_Get()
    {
        $data = $this->FunctionHelperGET('v00201', __FUNCTION__);

        if ($data !== NULL) {
            $data = (int)$data;

            if ($data === 5) {
                $result = false;
            } else {
                $result = true;
                $this->SetBuffer('feature_afterheater', '1');
            }

            $this->SetValue_IfDifferent('AfterheaterState', $result);
            return $result;
        }

        return NULL;
    }


    public function AllData_Get(bool $live)
    {
        if ($live === false) {
            $dataAR = $this->GetBufferX('MultiBuffer_DataListAll');
            if (@count($dataAR) > 0) {
                return $dataAR;
            }
        }

        return $this->Data_List_All();
    }


    public function AllData_Combined_Get(bool $live)
    {
        if ($live === false) {
            $dataAR = $this->GetBufferX('MultiBuffer_KWLdataAR_combined');
            if (@count($dataAR) > 0) {
                return $dataAR;
            }
        }

        return $this->BasicInit(true);
    }


    public function Bypass_Get()
    {
        $result = $this->FunctionHelperGET('v02119', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);

            $this->SetValue_IfDifferent('Bypass', $result);
        }

        return $result;
    }


    public function CO2Control_Get()
    {
        $result = $this->FunctionHelperGET('v00037', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('CO2Control', $result);
        }

        return $result;
    }


    // 0 = aus, 1 = stufig, 2 = stufenlos
    public function CO2Control_Set(int $value)
    {
        $vID = 'v00037';

        if (($value >= 0) || ($value <= 2)) {
            $result = $this->FunctionHelperSET($vID, $value);
            $this->SetValue_IfDifferent('CO2Control', $value);
            return $result;
        }

        return false;
    }


    // number = 1 bis 8
    public function CO2Sensor_Get(int $number)
    {
        if ($number === 1) {
            $vID_text = 'v01081';
            $vID_value = 'v00128';
        } elseif (($number > 1) && ($number <= 8)) {
            $vID_textX = 1080 + $number;
            $vID_text = 'v0' . $vID_textX;
            $vID_valueX = 127 + $number;
            $vID_value = 'v00' . $vID_valueX;
        } else {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Invalid sensor number') . ' // ' . $this->Translate('Number') . ' = ' . $number, 0, KL_ERROR);
            return false;
        }

        if ($this->GetBuffer($vID_value) === '1') {
            $resultAR = array();

            $dataTEXT = @urldecode($this->Map_ID_to_VA_DataAll($vID_text));
            if ($dataTEXT !== false) {
                $resultAR['Description'] = $dataTEXT;
            }

            $dataVOC = $this->FunctionHelperGET($vID_value, __FUNCTION__, true);
            if ($dataVOC !== NULL) {
                $resultAR['CO2_ppm'] = (int)$dataVOC;
                $this->SetValue_IfDifferent('SensorCO2_' . $number, $resultAR['CO2_ppm']);
            }

            if (@array_key_exists('Description', $resultAR) === false) {
                $resultAR['Description'] = false;
            }
            if (@array_key_exists('CO2_ppm', $resultAR) === true) {
                return $resultAR;
            }
        }

        return false;
    }


    public function CO2Sensors_All_Get()
    {
        $resultAR = array();
        for ($i = 1; $i <= 8; $i++) {
            $resultAR[$i] = $this->CO2Sensor_Get($i);
        }

        return $resultAR;
    }


    public function Defrost_State_Get()
    {
        $dataAR = $this->System_Messages_Status_Get();

        if ($dataAR[25] === '') {
            $resultAR['HeatExchanger'] = false;
        } else {
            $resultAR['HeatExchanger'] = true;
        }

        if ($dataAR[25] === '') {
            $resultAR['HotWaterRegister'] = false;
        } else {
            $resultAR['HotWaterRegister'] = true;
        }

        $this->SetValue_IfDifferent('DefrostStateHeatExchanger', $resultAR['HeatExchanger']);
        $this->SetValue_IfDifferent('DefrostStateHotWaterRegister', $resultAR['HotWaterRegister']);

        return $resultAR;
    }


    /**
     * DeviceImage_Get (get the image of the device)
     *
     * @return string|false
     */
    public function DeviceImage_Get()
    {
        $host = $this->ReadPropertyString('deviceip');
        $orderNumber = $this->System_OrderNumber_Get();

        $imageURL = 'http://' . $host . '/images/g' . $orderNumber . '.png';
        $image_baseRAW = $this->curl_GET($imageURL);
        $image_base64 = base64_encode($image_baseRAW);

        if (@strlen($image_base64) > 1000) {
            if ($this->ReadPropertyBoolean('show_deviceimage') === true) {
                $imageID = @$this->GetIDForIdent('Device_Image');
                if ($imageID > 0) {
                    IPS_SetMediaContent($imageID, $image_base64);
                    IPS_SendMediaEvent($imageID);
                } else {
                    return false;
                }
            }

            return $image_base64;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('The image from the device could not be downloaded'), 0, KL_ERROR);

        return false;
    }


    public function FanLevel_Get()
    {
        // depending on the activated operating mode/program, read out the associated current fan level
        $mode = $this->OperatingMode_Get();
        if ($mode === 2) {
            $vID_FanLevel = 'v00092';
        } elseif ($mode === 3) {
            $vID_FanLevel = 'v00097';
        } elseif ($mode === 4) {
            $vID_FanLevel = 'v00602';
        } else {
            $vID_FanLevel = 'v00102';
        }

        $result = $this->FunctionHelperGET($vID_FanLevel, __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('FanLevel', $result);
        }

        return $result;
    }


    public function FanLevel_Set(int $fanlevel)
    {
        $FanLevelMIN = (int)$this->GetBuffer('FanLevelMIN');
        $FanLevelMAX = (int)$this->GetBuffer('FanLevelMAX');

        if (($fanlevel >= $FanLevelMIN) || ($fanlevel <= $FanLevelMAX)) {
            $this->SetValue_IfDifferent('FanLevel', $fanlevel);
            $this->OperatingMode_Set('manu');
            IPS_Sleep(1000);
            $result_fanlevel = $this->FunctionHelperSET('v00102', $fanlevel);
            IPS_Sleep(1000);
            $this->OperatingMode_Get();
            $this->FanLevel_Percent_Get();
            return $result_fanlevel;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0034 // ' . $this->Translate('The defined fan level is lower than the minimum allowed fan level or higher than the maximum possible fan level - processing is aborted') . ' // ' . $this->Translate('Defined fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
        return false;
    }


    public function FanLevel_SetForPeriod(int $fanlevel, int $minutes)
    {
        $FanLevelMIN = (int)$this->GetBuffer('FanLevelMIN');
        $FanLevelMAX = (int)$this->GetBuffer('FanLevelMAX');

        if (($fanlevel >= $FanLevelMIN) || ($fanlevel <= $FanLevelMAX)) {
            $this->SetValue_IfDifferent('FanLevel', $fanlevel);
            $this->OperatingMode_Set('manu');
            IPS_Sleep(1000);
            $result_fanlevel = $this->FunctionHelperSET('v00102', $fanlevel);
            IPS_Sleep(1000);
            $this->OperatingMode_Get();
            $this->FanLevel_Percent_Get();

            // Start timer with for given period
            $this->SetBuffer('FanLevel_Period_Seconds', $minutes * 60);
            $this->Timer_Control('FanLevel_Period', 1);

            return $result_fanlevel;
        }

        $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0034 // ' . $this->Translate('The defined fan level is lower than the minimum allowed fan level or higher than the maximum possible fan level - processing is aborted') . ' // ' . $this->Translate('Defined fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
        return false;
    }


    public function FanLevel_Percent_Get()
    {
        $result = $this->FunctionHelperGET('v00103', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('FanLevelPercent', $result);
        }

        return $result;
    }


    public function FanSpeed_ExhaustAir_Get()
    {
        $result = $this->FunctionHelperGET('v00349', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('FanSpeedExtractAir', $result);
        }

        return $result;
    }


    public function FanSpeed_SupplyAir_Get()
    {
        $result = $this->FunctionHelperGET('v00348', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('FanSpeedSupplyAir', $result);
        }

        return $result;
    }


    public function Filter_ChangeInterval_Get()
    {
        $result = $this->FunctionHelperGET('v01032', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('FilterChangeInterval', $result);
        }

        return $result;
    }


    public function Filter_ChangeInterval_Set(int $value)
    {
        $vID = 'v01032';

        if (($value >= 3) || ($value <= 12)) {
            return $this->FunctionHelperSET($vID, $value);
        }

        return false;
    }


    public function Filter_RemainingDays_Get()
    {
        $result = $this->FunctionHelperGET('v01033', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = round((int)$result / 60 / 24);

            $this->SetValue_IfDifferent('FilterRemainingTime', $result);

            if ($result <= $this->ReadPropertyInteger('filterremainingdayswarning')) {
                $this->SetValue_IfDifferent('FilterRemainingTimeBOOL', true);
                $this->Notification('filter');
            } else {
                $this->SetValue_IfDifferent('FilterRemainingTimeBOOL', false);
                $this->Notification_Helper('filter', true);
            }
        }

        return $result;
    }


    public function Filter_Reset()
    {
        $filterInterval = $this->Filter_ChangeInterval_Get();
        $result = NULL;
        if ($filterInterval !== NULL) {
            $filterIntervalHours = $filterInterval * 30 * 24 * 60;
            $postData = 'v01034=1&v01033='.$filterIntervalHours;

            $result = $this->FunctionHelperSETcustom('gear.htm', $postData);
            IPS_Sleep(2000);
            $this->Filter_RemainingDays_Get();
        }

        return $result;
    }


    /**
     * HeatRecoveryEfficiency_Calculate (Calculate heat recovery efficiency according to VDI 2071)
     *
     * @return int|false
     */
    public function HeatRecoveryEfficiency_Calculate()
    {
        $temp_outsideair = false;
        $temp_supplyair = false;
        $temp_extractedair = false;
        $temp_exhaustair = false;

        $TempSensorsAR = $this->Temperature_Sensors_All_Get();

        if (@array_key_exists('Value_C', $TempSensorsAR[1]) === true) {
            $temp_outsideair = $TempSensorsAR[1]['Value_C'];
        }
        if (@array_key_exists('Value_C', $TempSensorsAR[2]) === true) {
            $temp_supplyair = $TempSensorsAR[2]['Value_C'];
        }
        if (@array_key_exists('Value_C', $TempSensorsAR[3]) === true) {
            $temp_extractedair = $TempSensorsAR[3]['Value_C'];
        }
        if (@array_key_exists('Value_C', $TempSensorsAR[4]) === true) {
            $temp_exhaustair = $TempSensorsAR[4]['Value_C'];
        }

        if (($temp_supplyair !== false) && ($temp_outsideair !== false) && ($temp_extractedair !== false)) {
            $heatRecoveryEfficiency = round(($temp_supplyair - $temp_outsideair) / ($temp_extractedair - $temp_outsideair) * 100);

            $this->SetValue_IfDifferent('HeatRecoveryEfficiency', $heatRecoveryEfficiency);

            return $heatRecoveryEfficiency;
        }

        $this->SendDebug(__FUNCTION__, 'INFO // ' . $this->Translate('Not all necessary temperature sensors are connected to the system. The following temperature sensors are required to calculate the heat recovery efficiency: outside air, supply air, exhaust air'), 0);

        return false;
    }


    public function HumidityControl_Get()
    {
        $result = $this->FunctionHelperGET('v00033', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('HumidityControl', $result);
        }

        return $result;
    }


    // 0 = aus, 1 = stufig, 2 = stufenlos
    public function HumidityControl_Set(int $value)
    {
        $vID = 'v00033';

        if (($value >= 0) || ($value <= 2)) {
            $result = $this->FunctionHelperSET($vID, $value);
            $this->SetValue_IfDifferent('HumidityControl', $value);
            return $result;
        }

        return false;
    }


    public function HumidityControl_Internal_Get()
    {
        $result = $this->FunctionHelperGET('v02142', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);
            $this->SetValue_IfDifferent('HumidityControlInternal', $result);
        }

        return $result;
    }


    public function HumidityControl_Internal_ExhaustAir_Get()
    {
        $result = $this->FunctionHelperGET('v02136', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;
            $this->SetValue_IfDifferent('HumidityControlInternalExtractAir', $result);
        }

        return $result;
    }


    // 0 = aus, 1 = stufenlos
    public function HumidityControl_Internal_Set(bool $value)
    {
        $vID = 'v02142';

        if (($value === false) || ($value === true)) {
            $result = $this->FunctionHelperSET($vID, (int)$value);
            $this->SetValue_IfDifferent('HumidityControlInternal', $value);
            return $result;
        }

        return false;
    }


    // number = 1 bis 8
    public function HumiditySensor_Get(int $number)
    {
        if ($number === 1) {
            $vID1 = 'v00111';
            $vID2 = 'v00119';
            $vID3 = 'v01071';
        } elseif (($number > 1) && ($number <= 8)) {
            $vID1x = 110 + $number;
            $vID1 = 'v00' . $vID1x;
            $vID2x = 118 + $number;
            $vID2 = 'v00' . $vID2x;
            $vID3x = 1070 + $number;
            $vID3 = 'v0' . $vID3x;
        } else {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Invalid sensor number') . ' // ' . $this->Translate('Number') . ' = ' . $number, 0, KL_ERROR);
            return false;
        }

        $resultAR = array();

        if ($this->GetBuffer($vID1) === '1') {
            $dataTEXT = @urldecode($this->Map_ID_to_VA_DataAll($vID3));
            if ($dataTEXT !== false) {
                $resultAR['Description'] = $dataTEXT;
            }

            $dataRH = $this->FunctionHelperGET($vID1, __FUNCTION__, true);
            if ($dataRH !== NULL) {
                $resultAR['RelativeHumidity'] = (int)$dataRH;
                $this->SetValue_IfDifferent('SensorHumidityRH_' . $number, $resultAR['RelativeHumidity']);
            }

            $dataTC = $this->FunctionHelperGET($vID2, __FUNCTION__, true);
            if ($dataTC !== NULL) {
                $resultAR['TemperatureCelcius'] = (float)$dataTC;
                $this->SetValue_IfDifferent('SensorHumidityTC_' . $number, $resultAR['TemperatureCelcius']);
            }
        }

        if (@array_key_exists('Description', $resultAR) === false) {
            $resultAR['Description'] = false;
        }
        if (@array_key_exists('RelativeHumidity', $resultAR) === false) {
            $resultAR['RelativeHumidity'] = false;
        }
        if (@array_key_exists('TemperatureCelcius', $resultAR) === false) {
            $resultAR['TemperatureCelcius'] = false;
        }
        if (($resultAR['RelativeHumidity'] !== false) || ($resultAR['TemperatureCelcius'] !== false)) {
            return $resultAR;
        }

        return false;
    }


    public function HumiditySensors_All_Get()
    {
        $resultAR = array();
        for ($i = 1; $i <= 8; $i++) {
            $resultAR[$i] = $this->HumiditySensor_Get($i);
        }

        return $resultAR;
    }


    public function OperatingMode_Get()
    {
        return $this->OperatingMode_GetInternal();
    }


    public function OperatingMode_Party_Set(bool $activate, int $fanlevel, int $duration)
    {
        $vID_Duration = 'v00091';
        $vID_FanLevel = 'v00092';
        $vID_Mode = 'v00094';

        // Deactivate mode?
        if ($activate === false) {
            $postData = $vID_Duration . '=0&' . $vID_Mode . '=0';
            $result = $this->FunctionHelperSETcustom('party.htm', $postData);
            IPS_Sleep(2000);
            $this->OperatingMode_Get();

            return $result;
        }

        // Is another mode active that has to be stopped first?
        $currentMode = $this->OperatingMode_GetInternal(false);
        if (($currentMode === 3) || ($currentMode === 4)) {
            $this->OperatingMode_Cancel($currentMode);
        }

        // Determine smallest permitted fan level
        $fanLevelMIN = $this->GetBuffer('FanLevelMIN');
        if ($fanLevelMIN === '') {
            $dataMIN = $this->System_FanLevelMin_Get();
            if ($dataMIN === NULL) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0019 // ' . $this->Translate('The smallest permitted fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($dataMIN), 0, KL_ERROR);
                return false;
            }
            $fanLevelMIN = (int)$dataMIN;
        }

        // Determine largest permitted fan level
        $fanLevelMAX = $this->GetBuffer('FanLevelMAX');
        if ($fanLevelMAX === '') {
            $fanDataAR = $this->FanLevel_Range_Determine();
            if (@array_key_exists('FanLevelMAX', $fanDataAR) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0020 // ' . $this->Translate('The largest permissible fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($fanDataAR), 0, KL_ERROR);
                return false;
            }
            $fanLevelMAX = $fanDataAR['FanLevelMAX'];
        }

        // Check for valid parameters
        if (($fanlevel < $fanLevelMIN) || ($fanlevel > $fanLevelMAX) || ($duration > 180) || ($duration < 5)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0021 // ' . $this->Translate('Invalid parameter value') . ' // ' . $this->Translate('Fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $fanLevelMIN . '-' . $fanLevelMAX . ' // ' . $this->Translate('Duration') . ' = ' . $duration . ' // ' . $this->Translate('Duration (min-max)') . ' = 5-180', 0, KL_ERROR);
            return false;
        }

        // Activate mode
        $durationRoundTo5 = round($duration / 5) * 5;
        $postData = $vID_FanLevel . '=' . $fanlevel . '&' . $vID_Duration . '=' . $durationRoundTo5 . '&' . $vID_Mode . '=1';
        $result = $this->FunctionHelperSETcustom('party.htm', $postData);
        IPS_Sleep(2000);
        $this->OperatingMode_Get();

        return $result;
    }


    public function OperatingMode_Party_SetWithPresets()
    {
        $FanLevelMIN = (int)$this->GetBuffer('FanLevelMIN');
        $FanLevelMAX = (int)$this->GetBuffer('FanLevelMAX');

        $duration = $this->GetValue('OperatingModePresetPartyDuration');
        $fanlevel = $this->GetValue('OperatingModePresetPartyFanLevel');

        // Check for invalid presets
        if (($fanlevel < $FanLevelMIN) || ($fanlevel > $FanLevelMAX)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0034 // ' . $this->Translate('The defined fan level is lower than the minimum allowed fan level or higher than the maximum possible fan level - processing is aborted') . ' // ' . $this->Translate('Defined fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }
        if (($duration < 5) || ($duration > 180)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0035 // ' . $this->Translate('The set duration is shorter or longer than the allowed duration - processing is aborted') . ' // ' . $this->Translate('Defined duration') . ' = ' . $fanlevel . ' // ' . $this->Translate('Duration (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }

        return $this->OperatingMode_Party_Set(true, $fanlevel, $duration);
    }


    public function OperatingMode_RemainingMinutes_Get()
    {
        // depending on the activated operating mode/program, read out the associated remaining time
        switch ($this->OperatingMode_Get()) {
            case 2:
                $vID_ModeRemainingTime = 'v00093';
                break;

            case 3:
                $vID_ModeRemainingTime = 'v00098';
                break;

            default:
                $vID_ModeRemainingTime = false;
                break;
        }

        if ($vID_ModeRemainingTime !== false) {
            $result = $this->FunctionHelperGET($vID_ModeRemainingTime, __FUNCTION__, true);

            if ($result !== NULL) {
                $result = (int)$result;

                return $result;
            }
        }

        return false;
    }


    public function OperatingMode_Set(string $value)
    {
        $mode_lowercase = strtolower($value);
        $mode_int = (int)$value;
        if (($mode_lowercase === 'man') || ($mode_lowercase === 'manu') || ($mode_lowercase === 'manual') || ($mode_lowercase === 'manuell') || ($mode_int === 1)) {
            $mode = 1;
        } else {
            $mode = 0;
        }

        $this->SetValue_IfDifferent('OperatingMode', $mode);

        // Is another mode active that has to be stopped first?
        $currentMode = $this->OperatingMode_GetInternal(false);
        if (($currentMode === 2) || ($currentMode === 3) || ($currentMode === 4)) {
            $this->OperatingMode_Cancel($currentMode);
        }

        return $this->FunctionHelperSET('v00101', $mode);
    }


    public function OperatingMode_Vacation_Set(int $program, int $fanlevel, string $dateStart, string $dateEnd, int $intervalTime, int $activationPeriod)
    {
        // URLAUB
        // program = 0 Aus, 1 Intervall, 2 Konstant  // v00601
        // type = 0 Ab-/Zuluft (Lüfterstufe einstellbar) , 1 Zuluft (Lüfterstufe fest auf 2) , 2 Abluft (Lüfterstufe fest auf 2)
        // fanlevel = allgemein min und max // v00602
        // dateStart = DD.MM.YYYY oder MM.DD.YYYY oder YYYY.MM.DD // v00603
        // dateEnd = DD.MM.YYYY oder MM.DD.YYYY oder YYYY.MM.DD // v00604
        // intervalTime = 1 bis 24 (stunden) // v00605  >> nur bei Programm "Intervall" verfügbar
        // activationPeriod = 5 bis 300 (minuten) // v00606  >> nur bei Programm "Intervall" verfügbar

        $vID_Mode = 'v00601';
        $vID_FanLevel = 'v00602';
        $vID_DateStart = 'v00603';
        $vID_DateEnd = 'v00604';
        $vID_IntervalTime = 'v00605';
        $vID_activationPeriod = 'v00606';

        // Deactivate mode?
        if ($program === 0) {
            $postData = $vID_Mode . '=0';
            $result = $this->FunctionHelperSETcustom('urlaub.htm', $postData);
            IPS_Sleep(2000);
            $this->OperatingMode_Get();

            return $result;
        }

        // Is another mode active that has to be stopped first?
        $currentMode = $this->OperatingMode_GetInternal(false);
        if (($currentMode === 2) || ($currentMode === 3)) {
            $this->OperatingMode_Cancel($currentMode);
        }

        // Check date start
        if ($this->DateFormat_Check($dateStart) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0022 // ' . $this->Translate('The entered date (start) is invalid or in the wrong format - valid format is ') . ' // ' . $this->Translate('Date entered') . ' = ' . $this->DataToString($dateStart), 0, KL_ERROR);
            return false;
        }

        // Check date end
        if ($this->DateFormat_Check($dateStart) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0023 // ' . $this->Translate('The entered date (end) is invalid or in the wrong format - valid format is ') . ' // ' . $this->Translate('Date entered') . ' = ' . $this->DataToString($dateEnd), 0, KL_ERROR);
            return false;
        }

        // Determine smallest permitted fan level
        $fanLevelMIN = $this->GetBuffer('FanLevelMIN');
        if ($fanLevelMIN === '') {
            $dataMIN = $this->System_FanLevelMin_Get();
            if ($dataMIN === NULL) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0024 // ' . $this->Translate('The smallest permitted fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($dataMIN), 0, KL_ERROR);
                return false;
            }
            $fanLevelMIN = (int)$dataMIN;
        }

        // Determine largest permitted fan level
        $fanLevelMAX = $this->GetBuffer('FanLevelMAX');
        if ($fanLevelMAX === '') {
            $fanDataAR = $this->FanLevel_Range_Determine();
            if (@array_key_exists('FanLevelMAX', $fanDataAR) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0025 // ' . $this->Translate('The largest permissible fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($fanDataAR), 0, KL_ERROR);
                return false;
            }
            $fanLevelMAX = $fanDataAR['FanLevelMAX'];
        }

        // Check for valid parameters
        if (($program < 0) || ($program > 2) || ($fanlevel < $fanLevelMIN) || ($fanlevel > $fanLevelMAX) || ($intervalTime < 1) || ($intervalTime > 24) || ($activationPeriod < 5) || ($activationPeriod > 300)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0026 // ' . $this->Translate('Invalid parameter value') . ' // ' . $this->Translate('Program') . ' = ' . $program . ' // ' . $this->Translate('Program (min-max)') . ' = 0-2 // ' . $this->Translate('Fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $fanLevelMIN . '-' . $fanLevelMAX . ' // ' . $this->Translate('Interval time') . ' = ' . $intervalTime . ' // ' . $this->Translate('Interval time (min-max)') . ' = 1-24' . ' // ' . $this->Translate('On time') . ' = ' . $activationPeriod . ' // ' . $this->Translate('On time (min-max)') . ' = 5-300', 0, KL_ERROR);
            return false;
        }

        // Activate mode
        $postData = '';
        if ($program === 1) {
            $postData = $vID_Mode . '=1&' . $vID_FanLevel . '=' . $fanlevel . '&' . $vID_DateStart . '=' . $dateStart . '&' . $vID_DateEnd . '=' . $dateEnd . '&' . $vID_IntervalTime . '=' . $intervalTime . '&' . $vID_activationPeriod . '=' . $activationPeriod;
        } elseif ($program === 2) {
            $aw_IntervalTime = $this->FunctionHelperGET($vID_IntervalTime, __FUNCTION__, true);
            $aw_ActivationPeriod = $this->FunctionHelperGET($vID_activationPeriod, __FUNCTION__, true);
            $postData = $vID_Mode . '=2&' . $vID_FanLevel . '=' . $fanlevel . '&' . $vID_DateStart . '=' . $dateStart . '&' . $vID_DateEnd . '=' . $dateEnd . '&' . $vID_IntervalTime . '=' . $aw_IntervalTime . '&' . $vID_activationPeriod . '=' . $aw_ActivationPeriod;
        }
        $result = $this->FunctionHelperSETcustom('urlaub.htm', $postData);
        IPS_Sleep(2000);
        $this->OperatingMode_Get();

        return $result;
    }


    public function OperatingMode_Vacation_SetWithPresets()
    {
        $FanLevelMIN = (int)$this->GetBuffer('FanLevelMIN');
        $FanLevelMAX = (int)$this->GetBuffer('FanLevelMAX');

        $program = $this->GetValue('OperatingModePresetVacationProgram');
        $fanlevel = $this->GetValue('OperatingModePresetVacationFanLevel');
        $dateStartX = $this->GetValue('OperatingModePresetVacationDateStart');
        $dateEndX = $this->GetValue('OperatingModePresetVacationDateEnd');
        $dateStart = date('d', $dateStartX) . '.' . date('m', $dateStartX) . '.' . date('Y', $dateStartX);
        $dateEnd = date('d', $dateEndX) . '.' . date('m', $dateEndX) . '.' . date('Y', $dateEndX);
        $intervalTime = $this->GetValue('OperatingModePresetVacationIntervalTime');
        $activationPeriod = $this->GetValue('OperatingModePresetVacationActivationPeriod');

        // Convert the program number to the number expected by easyControls
        if ($program === 0) {
            $program = 1;
        } elseif ($program === 1) {
            $program = 2;
        }

        // Check for invalid presets
        if ($dateStartX < time()) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0031 // ' . $this->Translate('The start date is in the past - processing is canceled') . ' // ' . $this->Translate('Date start') . ' = ' . $dateStart, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }
        if ($dateStartX > $dateEndX) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0032 // ' . $this->Translate('The end date is before the start date - processing is canceled') . ' // ' . $this->Translate('Date start') . ' = ' . $dateStart . ' // ' . $this->Translate('Date end') . ' = ' . $dateEnd, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }
        if (($program === 0) || ($intervalTime < 1) || ($intervalTime > 24) || ($activationPeriod < 5) || ($activationPeriod > 300)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0033 // ' . $this->Translate('The function is aborted because of one or more invalid parameters') . ' // ' . $this->Translate('Program') . ' = ' . $program . ' // ' . $this->Translate('Interval time') . ' = ' . $intervalTime . ' // ' . $this->Translate('On time') . ' = ' . $activationPeriod, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }
        if (($fanlevel < $FanLevelMIN) || ($fanlevel > $FanLevelMAX)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0034 // ' . $this->Translate('The defined fan level is lower than the minimum allowed fan level or higher than the maximum possible fan level - processing is aborted') . ' // ' . $this->Translate('Defined fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }

        return $this->OperatingMode_Vacation_Set($program, $fanlevel, $dateStart, $dateEnd, $intervalTime, $activationPeriod);
    }


    public function OperatingMode_Whisper_Set(bool $activate, int $fanlevel, int $duration)
    {
        $vID_Duration = 'v00096';
        $vID_FanLevel = 'v00097';
        $vID_Mode = 'v00099';

        // Deactivate mode?
        if ($activate === false) {
            $postData = $vID_Duration . '=0&' . $vID_Mode . '=0';
            $result = $this->FunctionHelperSETcustom('ruhe.htm', $postData);
            IPS_Sleep(2000);
            $this->OperatingMode_Get();

            return $result;
        }

        // Is another mode active that has to be stopped first?
        $currentMode = $this->OperatingMode_GetInternal(false);
        if (($currentMode === 2) || ($currentMode === 4)) {
            $this->OperatingMode_Cancel($currentMode);
        }

        // Determine smallest permitted fan level
        $fanLevelMIN = $this->GetBuffer('FanLevelMIN');
        if ($fanLevelMIN === '') {
            $dataMIN = $this->System_FanLevelMin_Get();
            if ($dataMIN === NULL) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0016 // ' . $this->Translate('The smallest permitted fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($dataMIN), 0, KL_ERROR);
                return false;
            }
            $fanLevelMIN = (int)$dataMIN;
        }

        // Determine largest permitted fan level
        $fanLevelMAX = $this->GetBuffer('FanLevelMAX');
        if ($fanLevelMAX === '') {
            $fanDataAR = $this->FanLevel_Range_Determine();
            if (@array_key_exists('FanLevelMAX', $fanDataAR) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0017 // ' . $this->Translate('The largest permissible fan level could not be determined') . ' // ' . $this->Translate('Data') . ' = ' . $this->DataToString($fanDataAR), 0, KL_ERROR);
                return false;
            }
            $fanLevelMAX = $fanDataAR['FanLevelMAX'];
        }

        // Check for valid parameters
        if (($fanlevel < $fanLevelMIN) || ($fanlevel > $fanLevelMAX) || ($duration > 180) || ($duration < 5)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0018 // ' . $this->Translate('Invalid parameter value') . ' // ' . $this->Translate('Fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $fanLevelMIN . '-' . $fanLevelMAX . ' // ' . $this->Translate('Duration') . ' = ' . $duration . ' // ' . $this->Translate('Duration (min-max)') . ' = 5-80', 0, KL_ERROR);
            return false;
        }

        // Activate mode
        $durationRoundTo5 = round($duration / 5) * 5;
        $postData = $vID_FanLevel . '=' . $fanlevel . '&' . $vID_Duration . '=' . $durationRoundTo5 . '&' . $vID_Mode . '=1';
        $result = $this->FunctionHelperSETcustom('ruhe.htm', $postData);
        IPS_Sleep(2000);
        $this->OperatingMode_Get();

        return $result;
    }


    public function OperatingMode_Whisper_SetWithPresets()
    {
        $FanLevelMIN = (int)$this->GetBuffer('FanLevelMIN');
        $FanLevelMAX = (int)$this->GetBuffer('FanLevelMAX');

        $duration = $this->GetValue('OperatingModePresetWhisperDuration');
        $fanlevel = $this->GetValue('OperatingModePresetWhisperFanLevel');

        // Check for invalid presets
        if (($fanlevel < $FanLevelMIN) || ($fanlevel > $FanLevelMAX)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0034 // ' . $this->Translate('The defined fan level is lower than the minimum allowed fan level or higher than the maximum possible fan level - processing is aborted') . ' // ' . $this->Translate('Defined fan level') . ' = ' . $fanlevel . ' // ' . $this->Translate('Fan level (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }
        if (($duration < 5) || ($duration > 180)) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0035 // ' . $this->Translate('The set duration is shorter or longer than the allowed duration - processing is aborted') . ' // ' . $this->Translate('Defined duration') . ' = ' . $fanlevel . ' // ' . $this->Translate('Duration (min-max)') . ' = ' . $FanLevelMIN . '-' . $FanLevelMAX, 0, KL_ERROR);
            $this->OperatingMode_Get();
            return false;
        }

        return $this->OperatingMode_Whisper_Set(true, $fanlevel, $duration);
    }


    public function Preheater_ActualPower_Get()
    {
        $result = $this->FunctionHelperGET('v02117', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('PreheaterActualPower', $result);
        }

        return $result;
    }


    public function Preheater_HeatOutputDelivered_Get()
    {
        $result1 = $this->FunctionHelperGET('v01105', __FUNCTION__, true);
        $result2 = $this->FunctionHelperGET('v01108', __FUNCTION__, true);

        $result = NULL;
        if (($result1 !== NULL) && ($result2 !== NULL)) {
            if ((float)$result1 === 0) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0028 // ' . $this->Translate('Implausible data - abort') . ' // result1 = ' . $result1 . ' // result2 = ' . $result2, 0, KL_ERROR);
                return false;
            }

            if ((float)$result2 === 0) {
                $result = 0;
            } else {
                $result = round(((float)$result2 / (float)$result1) * 100, 1);
            }

            $this->SetValue_IfDifferent('PreheaterHeatOutputDelivered', $result);
        }

        return $result;
    }


    public function Preheater_Status_Get()
    {
        $data = $this->FunctionHelperGET('v00024', __FUNCTION__);

        if ($data !== NULL) {
            $result = $this->DataToBool($data);
            $this->SetValue_IfDifferent('PreheaterState', $result);
            if ($result === true) {
                $this->SetBuffer('feature_preheater', '1');
            }
            return $result;
        }

        return NULL;
    }


    public function System_AUTOatMidnight_Get()
    {
        $result = $this->FunctionHelperGET('v02116', __FUNCTION__);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);

            $this->SetValue_IfDifferent('SettingAUTOatMidnight', $result);
        }

        return $result;
    }


    public function System_AUTOatMidnight_Set(bool $value)
    {
        if ($value === false) {
            $postValue = 0;
        } else {
            $postValue = 'on';
        }

        $result = $this->FunctionHelperSET('v02116', $postValue);
        $this->SetValue_IfDifferent('SettingAUTOatMidnight', $value);

        return $result;
    }


    public function System_CloudSync_Get()
    {
        $result = $this->FunctionHelperGET('v00008', __FUNCTION__);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);

            $this->SetValue_IfDifferent('SettingCloudSync', $result);
        }

        return $result;
    }


    public function System_CloudSync_Set(bool $value)
    {
        if ($value === false) {
            $postValue = 0;
        } else {
            $postValue = 'on';
        }

        $result = $this->FunctionHelperSET('v00008', $postValue);
        $this->SetValue_IfDifferent('SettingCloudSync', $value);

        return $result;
    }


    public function System_Date_Get()
    {
        return $this->FunctionHelperGET('v00004', __FUNCTION__);
    }


    public function System_DateFormat_Get()
    {
        $result = $this->FunctionHelperGET('v00052', __FUNCTION__, true);

        if ($result !== NULL) {
            $this->SetBuffer('DateFormat', $result);
        }

        return $result;
    }


    public function System_DaylightSavingTimeMode_Get()
    {
        $result = $this->FunctionHelperGET('v00006', __FUNCTION__);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);
        }

        return $result;
    }


    public function System_DaylightSavingTimeMode_Set(bool $value)
    {
        if ($value === false) {
            $postValue = 0;
        } else {
            $postValue = 'on';
        }
        return $this->FunctionHelperSET('v00006', $postValue);
    }


    public function System_FanLevelMin_Get()
    {
        $result = $this->FunctionHelperGET('v00020', __FUNCTION__);

        if ($result !== NULL) {
            $result = (int)$result;
            $this->SetBuffer('FanLevelMIN', $result);
        }

        return $result;
    }


    public function System_Language_Get()
    {
        $dataAR = $this->Data_Get_Werte('werte4.xml');
        if (@array_key_exists('LANG', $dataAR) === true) {
            $language = $dataAR['LANG'];
            $this->SetBuffer('language', $language);
            if (IPS_GetKernelVersion() >= 5.1) {
                $this->WriteAttributeString('language', $language);
            }
            return $language;
        }

        return false;
    }


    public function System_MACAddress_Get()
    {
        return $this->FunctionHelperGET('v00002', __FUNCTION__);
    }


    public function System_Messages_DataExchange_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $data = $this->FunctionHelperGET('v02104', __FUNCTION__, true);

        if ($data !== NULL) {

            $textBitAR[0] = $this->Translate('No error');
            $textBitAR[1] = $this->Translate('Reserved');
            $textBitAR[2] = $this->Translate('DNS server not found (No internet?)');
            $textBitAR[3] = $this->Translate('Error when downloading a file (not available?)');
            $textBitAR[4] = $this->Translate('Error when calculating the checksum (incorrect checksum in the container?)');
            $textBitAR[5] = $this->Translate('Error when handling the SD card (not available?)');
            $textBitAR[6] = $this->Translate('Error when reading a file from the SD card');
            $textBitAR[7] = $this->Translate('Error when uploading a file');
            $textBitAR[8] = $this->Translate('Other error');
            $textBitAR[9] = $this->Translate('Error during copy operation');

            $data = (int)$data;
            if (($data >= 0) && ($data <= 9)) {
                $resultAR = array();
                for ($i = 0; $i <= 9; $i++) {
                    if ($data === $i + 1) {
                        $resultAR[$i + 1] = $textBitAR[$i];
                    } else {
                        $resultAR[$i + 1] = '';
                    }
                }

                $HTMLtable = $this->Messages_HTML_Generate($resultAR, 'Web-' . $this->Translate('Error'));

                if ($DebugActive === true) {
                    $this->SendDebug(__FUNCTION__, 'DEBUG // Array = ' . $this->DataToString($resultAR), 0);
                    $this->SendDebug(__FUNCTION__, 'DEBUG // HTML = ' . $HTMLtable, 0);
                }

                $this->SetValue_IfDifferent('SystemMsgsHTMLDataExchange', $HTMLtable);
                return $resultAR;
            }
        }

        return false;
    }


    public function System_Messages_Error_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $data = $this->FunctionHelperGET('v01303', __FUNCTION__, true);

        if ($data !== NULL) {

            $textBitAR[0] = $this->Translate('Fan speed error -supply air- (outside air)');
            $textBitAR[1] = $this->Translate('Fan speed error -extracted air- (exhaust)');
            $textBitAR[2] = $this->Translate('');
            $textBitAR[3] = $this->Translate('SD card error when writing the E-Eprom data with regard to -FLASH ring buffer FULL-');
            $textBitAR[4] = $this->Translate('Bus over-current');
            $textBitAR[5] = $this->Translate('');
            $textBitAR[6] = $this->Translate('BASIS:0-Xing error VHZ EH (0-Xing = Zero-Crossing, Null-passage detection)');
            $textBitAR[7] = $this->Translate('Extension module (Preheater):0-Xing Error VHZ EH');
            $textBitAR[8] = $this->Translate('Extension module (Afterheater):0-Xing Error NHZ EH');
            $textBitAR[9] = $this->Translate('BASIS: Internal temp. sensor error - (T1) –outside air- (missing or cable break)');
            $textBitAR[10] = $this->Translate('BASIS: Internal temp. sensor error - (T2) –supply air- (missing or cable break)');
            $textBitAR[11] = $this->Translate('BASIS: Internal temp. sensor error - (T3) -extracted air- (missing or cable break)');
            $textBitAR[12] = $this->Translate('BASIS: Internal temp. sensor error - (T4) -outgoing air- (missing or cable break)');
            $textBitAR[13] = $this->Translate('BASIS: Internal temp. sensor error - (T1) -outside air- (short circuit)');
            $textBitAR[14] = $this->Translate('BASIS: Internal temp. sensor error - (T2) -supply air- (short circuit)');
            $textBitAR[15] = $this->Translate('BASIS: Internal temp. sensor error - (T3) -extracted air- (short circuit)');
            $textBitAR[16] = $this->Translate('BASIS: Internal temp. sensor error - (T4) -outgoing air- (short circuit)');
            $textBitAR[17] = $this->Translate('Ext. module configured as preheater, but not available or failed');
            $textBitAR[18] = $this->Translate('Ext. module configured as afterheater, but not available or failed');
            $textBitAR[19] = $this->Translate('Ext. module (VHZ): Duct sensor (T5) -outside air- (missing or cable break)');
            $textBitAR[20] = $this->Translate('Ext. module (NHZ): Duct sensor (T6) -supply air- (missing or cable break)');
            $textBitAR[21] = $this->Translate('Ext. module (NHZ): Duct sensor (T7) -return WW-Register- (missing or cable break)');
            $textBitAR[22] = $this->Translate('Ext. module (VHZ): Duct sensor (T5) -outside air- (short circuit)');
            $textBitAR[23] = $this->Translate('Ext. module (NHZ): Duct sensor (T6) -supply air- (short circuit)');
            $textBitAR[24] = $this->Translate('Ext. module (NHZ): Duct sensor (T7) - return WW-Register- (short circuit)');
            $textBitAR[25] = $this->Translate('Ext. module (VHZ): Automatic safety limiter');
            $textBitAR[26] = $this->Translate('Ext. module (VHZ): Manual safety limiter');
            $textBitAR[27] = $this->Translate('Ext. module (NHZ): Automatic safety limiter');
            $textBitAR[28] = $this->Translate('Ext. module (NHZ): Manual safety limiter');
            $textBitAR[29] = $this->Translate('Ext. module (NHZ): Frost protection WW return < e.g. 5°C - measured via WW return (T7)');
            $textBitAR[30] = $this->Translate('Ext. module (NHZ): Frost protection WW-Register < e.g. 5°C - measured via supply air (T6)');
            $textBitAR[31] = $this->Translate('BASIS: Frost protection external WW Reg. supply air temp. < 5°C (PHI only)');

            if (strlen($data) === 32) {
                $binAR = str_split($data);
                $binAR = array_reverse($binAR);

                $resultAR = array();
                if (@array_key_exists('31', $binAR) === true) {
                    foreach ($binAR as $index => $binEntry) {
                        if ($binEntry === '1') {
                            $resultAR[$index + 1] = $textBitAR[$index];
                        } else {
                            $resultAR[$index + 1] = '';
                        }
                    }

                    $HTMLtable = $this->Messages_HTML_Generate($resultAR, $this->Translate('Error'));

                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'DEBUG // Array = ' . $this->DataToString($resultAR), 0);
                        $this->SendDebug(__FUNCTION__, 'DEBUG // HTML = ' . $HTMLtable, 0);
                    }

                    $this->SetValue_IfDifferent('SystemMsgsHTMLError', $HTMLtable);
                    return $resultAR;
                }
            }
        }

        return false;
    }


    public function System_Messages_ErrorCount_Get()
    {
        $result = $this->FunctionHelperGET('v01300', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            if ($result > 0) {
                $this->SetValue_IfDifferent('SystemError', true);
                $this->Notification('errors');
            } else {
                $this->SetValue_IfDifferent('SystemError', false);
                $this->Notification_Helper('errors', true);
            }
            $this->SetValue_IfDifferent('SystemErrorCount', $result);
        }

        return $result;
    }


    public function System_Messages_Info_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $data = $this->FunctionHelperGET('v01305', __FUNCTION__, true);

        if ($data !== NULL) {

            $textBitAR[0] = $this->Translate('Filter change');
            $textBitAR[1] = $this->Translate('Frost protection WT');
            $textBitAR[2] = $this->Translate('SD-Card Error');
            $textBitAR[3] = $this->Translate('Loss of bus components');
            $textBitAR[4] = $this->Translate('');
            $textBitAR[5] = $this->Translate('');
            $textBitAR[6] = $this->Translate('');
            $textBitAR[7] = $this->Translate('');

            if (strlen($data) === 8) {
                $binAR = str_split($data);
                $binAR = array_reverse($binAR);

                $resultAR = array();
                if (@array_key_exists('7', $binAR) === true) {
                    foreach ($binAR as $index => $binEntry) {
                        if ($binEntry === '1') {
                            $resultAR[$index + 1] = $textBitAR[$index];
                        } else {
                            $resultAR[$index + 1] = '';
                        }
                    }

                    $HTMLtable = $this->Messages_HTML_Generate($resultAR, $this->Translate('Info'));

                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'DEBUG // Array = ' . $this->DataToString($resultAR), 0);
                        $this->SendDebug(__FUNCTION__, 'DEBUG // HTML = ' . $HTMLtable, 0);
                    }

                    $this->SetValue_IfDifferent('SystemMsgsHTMLInfo', $HTMLtable);
                    return $resultAR;
                }
            }
        }

        return false;
    }


    public function System_Messages_InfoCount_Get()
    {
        $result = $this->FunctionHelperGET('v01302', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            if ($result > 0) {
                $this->SetValue_IfDifferent('SystemInfo', true);
            } else {
                $this->SetValue_IfDifferent('SystemInfo', false);
            }
            $this->SetValue_IfDifferent('SystemInfoCount', $result);
        }

        return $result;
    }


    public function System_Messages_Reset()
    {
        $postData = 'v01300=0&v01303=00000000000000000000000000000000&v01301=0&v01304=00000000&v01302=0&v01305=00000000&v02104=0&v01120=1&v02105=1';

        $result = $this->FunctionHelperSETcustom('fehl.htm', $postData);
        IPS_Sleep(2000);
        $this->Update_System_Data();

        return $result;
    }


    public function System_Messages_Status_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $data = $this->FunctionHelperGET('v01306', __FUNCTION__, true);

        if ($data !== NULL) {

            $textBitAR[0] = $this->Translate('System - Complete initialized');
            $textBitAR[1] = $this->Translate('System - software update active');
            $textBitAR[2] = $this->Translate('Firmware update is activated');
            $textBitAR[3] = $this->Translate('INBA is active');
            $textBitAR[4] = $this->Translate('Party mode');
            $textBitAR[5] = $this->Translate('Whisper mode');
            $textBitAR[6] = $this->Translate('Vacation mode');
            $textBitAR[7] = $this->Translate('Preheater is configured to BASIS module');
            $textBitAR[8] = $this->Translate('Preheater is configured to expansion module');
            $textBitAR[9] = $this->Translate('Expansion module preheater active');
            $textBitAR[10] = $this->Translate('Preheater configured');
            $textBitAR[11][0] = $this->Translate('Preheater type: Electric');
            $textBitAR[11][1] = $this->Translate('Preheater type: Brine (earth or air)');
            $textBitAR[12] = $this->Translate('Afterheater configured');
            $textBitAR[13] = $this->Translate('Expansion module afterheater active');
            $textBitAR[14] = $this->Translate('Afterheater active');
            $textBitAR[15][0] = $this->Translate('Afterheater type: Electric');
            $textBitAR[15][1] = $this->Translate('Afterheater type: Hot water');
            $textBitAR[16] = $this->Translate('CO2-Control on');
            $textBitAR[17] = $this->Translate('Humidity-Control on');
            $textBitAR[18] = $this->Translate('VOC-Control on');
            $textBitAR[19] = $this->Translate('');
            $textBitAR[20] = $this->Translate('Min. one external contact connected (EM or ES)');
            $textBitAR[21] = $this->Translate('External contact function active');
            $textBitAR[22] = $this->Translate('Allow external access');
            $textBitAR[23] = $this->Translate('External access active');
            $textBitAR[24] = $this->Translate('Defrost heat exchanger');
            $textBitAR[25] = $this->Translate('Defrost hot water coil');
            $textBitAR[26] = $this->Translate('Filter change due');
            $textBitAR[27][0] = $this->Translate('Configuration 1 - DIBt');
            $textBitAR[27][1] = $this->Translate('Configuration 2 - PHI');
            $textBitAR[28] = $this->Translate('BEC-UI deactivated via Web');
            $textBitAR[29] = $this->Translate('Locking the control unit');
            $textBitAR[30] = $this->Translate('Master Password required (wrong password entered too often)');
            $textBitAR[31] = $this->Translate('Fan level display in % on');

            if (strlen($data) === 32) {
                $binAR = str_split($data);
                $binAR = array_reverse($binAR);

                $resultAR = array();
                if (@array_key_exists('31', $binAR) === true) {
                    foreach ($binAR as $index => $binEntry) {

                        if (($index === 7) || ($index === 8) || ($index === 11)) {
                            if ($this->Preheater_Status_Get() === false) {
                                continue;
                            }
                        }
                        if ($index === 15) {
                            if ($this->Afterheater_Status_Get() === false) {
                                continue;
                            }
                        }

                        if ($binEntry === '1') {
                            if (@array_key_exists('1', $textBitAR[$index]) === true) {
                                $resultAR[$index + 1] = $textBitAR[$index][1];
                            } else {
                                $resultAR[$index + 1] = $textBitAR[$index];
                            }
                        } else {
                            if (@array_key_exists('0', $textBitAR[$index]) === true) {
                                $resultAR[$index + 1] = $textBitAR[$index][0];
                            } else {
                                $resultAR[$index + 1] = '';
                            }
                        }
                    }

                    $HTMLtable = $this->Messages_HTML_Generate($resultAR, 'Status');

                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'DEBUG // Array = ' . $this->DataToString($resultAR), 0);
                        $this->SendDebug(__FUNCTION__, 'DEBUG // HTML = ' . $HTMLtable, 0);
                    }

                    $this->SetValue_IfDifferent('SystemMsgsHTMLStatus', $HTMLtable);
                    return $resultAR;
                }
            }
        }

        return false;
    }


    public function System_Messages_Warning_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        $data = $this->FunctionHelperGET('v01304', __FUNCTION__, true);

        if ($data !== NULL) {

            $textBitAR[0] = $this->Translate('Internal humidity sensor is missing or is not sending data');
            $textBitAR[1] = $this->Translate('');
            $textBitAR[2] = $this->Translate('');
            $textBitAR[3] = $this->Translate('');
            $textBitAR[4] = $this->Translate('');
            $textBitAR[5] = $this->Translate('');
            $textBitAR[6] = $this->Translate('');
            $textBitAR[7] = $this->Translate('');

            if (strlen($data) === 8) {
                $binAR = str_split($data);
                $binAR = array_reverse($binAR);

                $resultAR = array();
                if (@array_key_exists('7', $binAR) === true) {
                    foreach ($binAR as $index => $binEntry) {
                        if ($binEntry === '1') {
                            $resultAR[$index + 1] = $textBitAR[$index];
                        } else {
                            $resultAR[$index + 1] = '';
                        }
                    }

                    $HTMLtable = $this->Messages_HTML_Generate($resultAR, $this->Translate('Warning'));

                    if ($DebugActive === true) {
                        $this->SendDebug(__FUNCTION__, 'DEBUG // Array = ' . $this->DataToString($resultAR), 0);
                        $this->SendDebug(__FUNCTION__, 'DEBUG // HTML = ' . $HTMLtable, 0);
                    }

                    $this->SetValue_IfDifferent('SystemMsgsHTMLWarning', $HTMLtable);
                    return $resultAR;
                }
            }
        }

        return false;
    }


    public function System_Messages_WarningCount_Get()
    {
        $result = $this->FunctionHelperGET('v01301', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            if ($result > 0) {
                $this->SetValue_IfDifferent('SystemWarning', true);
                $this->Notification('warnings');
            } else {
                $this->SetValue_IfDifferent('SystemWarning', false);
                $this->Notification_Helper('warnings', true);
            }
            $this->SetValue_IfDifferent('SystemWarningCount', $result);
        }

        return $result;
    }


    public function System_Modbus_Get()
    {
        $result = $this->FunctionHelperGET('v01200', __FUNCTION__);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);

            $this->SetValue_IfDifferent('SettingModbus', $result);
        }

        return $result;
    }

    public function System_Modbus_Set(bool $value)
    {
        if ($value === false) {
            $postValue = 0;
        } else {
            $postValue = 'on';
        }
        $result = $this->FunctionHelperSET('v01200', $postValue);
        $this->SetValue_IfDifferent('SettingModbus', $value);
        return $result;
    }


    public function System_OperatingHours_Afterheater_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($this->GetBuffer('feature_afterheater') !== '1') {
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'INFO // ' . $this->Translate('Component not available'), 0);
            }
            return false;
        }

        $result = $this->FunctionHelperGET('v01105', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result / 60;

            $this->SetValue_IfDifferent('OperatingHoursAfterheater', $result);
        }

        return $result;
    }


    public function System_OperatingHours_ExhaustAirFan_Get()
    {
        $result = $this->FunctionHelperGET('v01104', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result / 60;

            $this->SetValue_IfDifferent('OperatingHoursExtractAirFan', $result);
        }

        return $result;
    }


    public function System_OperatingHours_Preheater_Get()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($this->GetBuffer('feature_preheater') !== '1') {
            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'INFO // ' . $this->Translate('Component not available'), 0);
            }
            return false;
        }

        $result = $this->FunctionHelperGET('v01105', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result / 60;

            $this->SetValue_IfDifferent('OperatingHoursPreheater', $result);
        }

        return $result;
    }


    public function System_OperatingHours_SupplyAirFan_Get()
    {
        $result = $this->FunctionHelperGET('v01103', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result / 60;

            $this->SetValue_IfDifferent('OperatingHoursSupplyAirFan', $result);
        }

        return $result;
    }


    public function System_OperatingHours_System_Get()
    {
        $result = $this->FunctionHelperGET('v01102', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('OperatingHours', $result);
        }

        return $result;
    }


    public function System_OrderNumber_Get()
    {
        return $this->FunctionHelperGET('v00001', __FUNCTION__);
    }


    public function System_ProductionCode_Get()
    {
        $result = $this->FunctionHelperGET('v00304', __FUNCTION__);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('ProductionCode', $result);
            }
        }

        return $result;
    }


    public function System_SecurityNumber_Get()
    {
        $result = $this->FunctionHelperGET('v00343', __FUNCTION__);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('SecurityNumber', $result);
            }
        }

        return $result;
    }


    public function System_SensorControlSleepMode_Get()
    {
        $result_timeFrom = $this->FunctionHelperGET('v02149', __FUNCTION__, true);
        $result_timeTo = $this->FunctionHelperGET('v02150', __FUNCTION__, true);

        $result = NULL;
        if (($result_timeFrom !== NULL) && ($result_timeTo !== NULL)) {
            if (($result_timeFrom === '00:00') && ($result_timeTo === '00:00')) {
                $result = false;
            } else {
                $result = true;
            }

            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('SettingSensorControlSleepMode', $result);
                if ($result_timeFrom !== '00:00') {
                    $this->SetValue_IfDifferent('SettingSensorControlSleepModeFrom', $this->TimeConvert_HoursMinutes_to_Seconds($result_timeFrom));
                }
                if ($result_timeTo !== '00:00') {
                    $this->SetValue_IfDifferent('SettingSensorControlSleepModeTo', $this->TimeConvert_HoursMinutes_to_Seconds($result_timeTo));
                }
            }
        }

        return $result;
    }


    public function System_SensorControlSleepMode_Set(bool $value, string $timeFrom, string $timeTo)
    {
        if ($value === false) {
            $postData = 'v02149a=0&v02149=00:00&v02150=00:00';
        } else {
            if ((($timeFrom === '00:00') || ($timeFrom === '0:00') || ($timeFrom === '0:0') || ($timeFrom === '0')) && (($timeTo === '00:00') || ($timeTo === '0:00') || ($timeTo === '0:0') || ($timeTo === '0'))) {
                return false;
            }

            preg_match('|(\d\d?){1,2}(:)(\d\d)|', $timeFrom, $matchTime);
            if (@array_key_exists('3', $matchTime) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0036 // ' . $this->Translate('The specified time is invalid.') . ' // ' . $this->Translate('Data') . ' = ' . $timeFrom . ' // ' . $this->Translate('Valid timestamp format = HH:MM'), 0, KL_ERROR);
                return false;
            }
            preg_match('|(\d\d?){1,2}(:)(\d\d)|', $timeTo, $matchTime);
            if (@array_key_exists('3', $matchTime) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0037 // ' . $this->Translate('The specified time is invalid.') . ' // ' . $this->Translate('Data') . ' = ' . $timeTo . ' // ' . $this->Translate('Valid timestamp format = HH:MM'), 0, KL_ERROR);
                return false;
            }

            $postData = 'v02149a=1&v02149=' . @urldecode($timeFrom) . '&v02150=' . @urldecode($timeTo);

            $this->SetValue_IfDifferent('SettingSensorControlSleepModeFrom', $this->TimeConvert_HoursMinutes_to_Seconds($timeFrom));
            $this->SetValue_IfDifferent('SettingSensorControlSleepModeTo', $this->TimeConvert_HoursMinutes_to_Seconds($timeTo));
        }

        $result = $this->FunctionHelperSETcustom('fueh.htm', $postData);
        IPS_Sleep(2000);
        $this->System_SensorControlSleepMode_Get();

        return $result;
    }


    public function System_SensorControlSleepModeFROM_Get()
    {
        $result = $this->FunctionHelperGET('v02149', __FUNCTION__, true);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                if ($result !== '00:00') {
                    $this->SetValue_IfDifferent('SettingSensorControlSleepModeFrom', $this->TimeConvert_HoursMinutes_to_Seconds($result));
                }
            }
        }

        return $result;
    }


    public function System_SensorControlSleepModeFROM_Set(string $value)
    {
        preg_match('|(\d\d?){1,2}(:)(\d\d)|', $value, $matchTime);
        if (@array_key_exists('3', $matchTime) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0036 // ' . $this->Translate('The specified time is invalid.') . ' // ' . $this->Translate('Data') . ' = ' . $value . ' // ' . $this->Translate('Valid timestamp format = HH:MM'), 0, KL_ERROR);
            return false;
        }

        $timeFrom = $value;
        $timeTo = $this->TimeConvert_Seconds_to_HoursMinutes($this->GetValue('SettingSensorControlSleepModeTo'));
        $result = $this->System_SensorControlSleepMode_Set(true, $timeFrom, $timeTo);
        $this->SetValue_IfDifferent('SettingSensorControlSleepModeFrom', $this->TimeConvert_HoursMinutes_to_Seconds($value));

        return $result;
    }


    public function System_SensorControlSleepModeTO_Get()
    {
        $result = $this->FunctionHelperGET('v02150', __FUNCTION__, true);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                if ($result !== '00:00') {
                    $this->SetValue_IfDifferent('SettingSensorControlSleepModeTo', $this->TimeConvert_HoursMinutes_to_Seconds($result));
                }
            }
        }

        return $result;
    }


    public function System_SensorControlSleepModeTO_Set(string $value)
    {
        preg_match('|(\d\d?){1,2}(:)(\d\d)|', $value, $matchTime);
        if (@array_key_exists('3', $matchTime) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // 0x0036 // ' . $this->Translate('The specified time is invalid.') . ' // ' . $this->Translate('Data') . ' = ' . $value . ' // ' . $this->Translate('Valid timestamp format = HH:MM'), 0, KL_ERROR);
            return false;
        }

        $timeFrom = $this->TimeConvert_Seconds_to_HoursMinutes($this->GetValue('SettingSensorControlSleepModeFrom'));
        $timeTo = $value;
        $result = $this->System_SensorControlSleepMode_Set(true, $timeFrom, $timeTo);
        $this->SetValue_IfDifferent('SettingSensorControlSleepModeTo', $this->TimeConvert_HoursMinutes_to_Seconds($value));

        return $result;
    }


    public function System_SerialNumber_Get()
    {
        $result = $this->FunctionHelperGET('v00303', __FUNCTION__);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('SerialNumber', $result);
            }
        }

        return $result;
    }


    public function System_SoftwareUpdateAutomatic_Get()
    {
        $result = $this->FunctionHelperGET('v00007', __FUNCTION__);

        if ($result !== NULL) {
            $result = $this->DataToBool($result);

            $this->SetValue_IfDifferent('SettingSoftwareUpdateAutomatic', $result);
        }

        return $result;
    }


    public function System_SoftwareUpdateAutomatic_Set(bool $value)
    {
        if ($value === false) {
            $postValue = 0;
        } else {
            $postValue = 'on';
        }
        $result = $this->FunctionHelperSET('v00007', $postValue);
        $this->SetValue_IfDifferent('SettingSoftwareUpdateAutomatic', $value);
        return $result;
    }


    public function System_SoftwareVersion_Get()
    {
        $result = $this->FunctionHelperGET('v01101', __FUNCTION__);

        if ($result !== NULL) {
            $result = (float)$result;

            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('SoftwareVersion', $result);
            }
        }

        return $result;
    }


    public function System_Time_Get()
    {
        return $this->FunctionHelperGET('v00005', __FUNCTION__, true);
    }


    public function System_TimezoneGMT_Get()
    {
        return $this->FunctionHelperGET('v00051', __FUNCTION__);
    }


    public function System_Type_Get()
    {
        $result = $this->FunctionHelperGET('v00000', __FUNCTION__);

        if ($result !== NULL) {
            if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
                $this->SetValue_IfDifferent('DeviceType', $result);
            }
        }

        return $result;
    }


    public function Temperature_Comfort_Get()
    {
        $result = $this->FunctionHelperGET('v00043', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (float)$result;
        }

        return $result;
    }


    // number = 1 bis 7
    public function Temperature_Sensor_Get(int $number)
    {
        $sensors_vID_AR = array();
        $sensors_vID_AR[1]['vID'] = 'v00104';
        $sensors_vID_AR[1]['varIdent'] = 'TemperatureOutdoorAir';
        $sensors_vID_AR[2]['vID'] = 'v00105';
        $sensors_vID_AR[2]['varIdent'] = 'TemperatureSupplyAir';
        $sensors_vID_AR[3]['vID'] = 'v00107';
        $sensors_vID_AR[3]['varIdent'] = 'TemperatureExtractAir';
        $sensors_vID_AR[4]['vID'] = 'v00106';
        $sensors_vID_AR[4]['varIdent'] = 'TemperatureExhaustAir';
        $sensors_vID_AR[5]['vID'] = 'v00108';
        $sensors_vID_AR[5]['varIdent'] = 'TemperatureDuctOutdoorAir';
        $sensors_vID_AR[6]['vID'] = 'v00146';
        $sensors_vID_AR[6]['varIdent'] = 'TemperatureDuctSupplyAir';
        $sensors_vID_AR[7]['vID'] = 'v00110';
        $sensors_vID_AR[7]['varIdent'] = 'TemperatureReturnWWRegister';

        if (@array_key_exists($number, $sensors_vID_AR) === false) {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Invalid sensor number') . ' // ' . $this->Translate('Number') . ' = ' . $number, 0, KL_ERROR);
            return false;
        }

        $resultAR = array();
        if ($this->GetBuffer($sensors_vID_AR[$number]['vID']) === '1') {

            $dataTEXT = $this->Map_ID_to_TEXT_DataCombined($sensors_vID_AR[$number]['vID']);
            if ($dataTEXT !== false) {
                $resultAR['Description'] = $dataTEXT;
            }

            $dataVALUE = $this->FunctionHelperGET($sensors_vID_AR[$number]['vID'], __FUNCTION__, true);
            if ($dataVALUE !== NULL) {
                $resultAR['Value_C'] = (float)$dataVALUE;
                $this->SetValue_IfDifferent($sensors_vID_AR[$number]['varIdent'], $resultAR['Value_C']);
            }
        }

        if (@array_key_exists('Description', $resultAR) === false) {
            $resultAR['Description'] = false;
        }
        if (@array_key_exists('Value_C', $resultAR) === true) {
            return $resultAR;
        }

        return false;
    }


    public function Temperature_Sensors_All_Get()
    {
        $resultAR = array();

        for ($i = 1; $i <= 7; $i++) {
            $resultAR[$i] = $this->Temperature_Sensor_Get($i);
        }

        return $resultAR;
    }


    public function Update_Data()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $time_start_code = microtime(true);
        }

        $resultAR = array();

        $resultAR['Bypass'] = $this->Bypass_Get();
        $resultAR['CO2control'] = $this->CO2Control_Get();
        $resultAR['CO2Sensors'] = $this->CO2Sensors_All_Get();
        $resultAR['Defrost'] = $this->Defrost_State_Get();
        $resultAR['FanLevel'] = $this->FanLevel_Get();
        $resultAR['FanLevelPercent'] = $this->FanLevel_Percent_Get();
        if ($this->GetBuffer('v00348') === '1') {
            $resultAR['FanSpeedSupplyAir'] = $this->FanSpeed_SupplyAir_Get();
        }
        if ($this->GetBuffer('v00349') === '1') {
            $resultAR['FanSpeedExtractAir'] = $this->FanSpeed_ExhaustAir_Get();
        }
        $resultAR['FilterChangeIntervalMonths'] = $this->Filter_ChangeInterval_Get();
        $resultAR['FilterRemainingDays'] = $this->Filter_RemainingDays_Get();
        $heatRecoveryEfficiency = $this->HeatRecoveryEfficiency_Calculate();
        if ($heatRecoveryEfficiency !== false) {
            $resultAR['HeatRecoveryEfficiency'] = $heatRecoveryEfficiency;
        }
        $resultAR['HumidityControl'] = $this->HumidityControl_Get();
        $resultAR['HumidityControlInternal'] = $this->HumidityControl_Internal_Get();
        $resultAR['HumidityControlInternalExtractAir'] = $this->HumidityControl_Internal_ExhaustAir_Get();
        $resultAR['HumiditySensors'] = $this->HumiditySensors_All_Get();
        $resultAR['OperatingModeRemainingMinutes'] = $this->OperatingMode_RemainingMinutes_Get();
        $resultAR['OperationMode'] = $this->OperatingMode_Get();
        if ($this->GetBuffer('feature_preheater') === '1') {
            $resultAR['Preheater']['State'] = $this->Preheater_Status_Get();
            $resultAR['Preheater']['ActualPower'] = $this->Preheater_ActualPower_Get();
            $resultAR['Preheater']['HeatOutputDelivered'] = $this->Preheater_HeatOutputDelivered_Get();
        }
        if ($this->GetBuffer('feature_afterheater') === '1') {
            $resultAR['Afterheater']['State'] = $this->Afterheater_Status_Get();
            $resultAR['Afterheater']['ActualPower'] = $this->Afterheater_ActualPower_Get();
            $resultAR['Afterheater']['HeatOutputDelivered'] = $this->Afterheater_HeatOutputDelivered_Get();
        }
        $resultAR['TemperatureComfort'] = $this->Temperature_Comfort_Get();
        $resultAR['TemperatureSensors'] = $this->Temperature_Sensors_All_Get();
        $resultAR['VOCcontrol'] = $this->VOCControl_Get();
        $resultAR['VOCSensors'] = $this->VOCSensors_All_Get();
        $resultAR['WeekProgram'] = $this->WeekProgram_Get();
        $resultAR['System'] = $this->Update_System_Data();

        ksort($resultAR);

        if ($DebugActive === true) {
            $duration_code = round(microtime(true) - $time_start_code, 2);
            $this->SendDebug(__FUNCTION__, $this->Translate('DURATION') . ' = ' . $duration_code . ' ' . $this->Translate('seconds'), 0);
            $this->SendDebug(__FUNCTION__, 'DEBUG // resultAR = ' . $this->DataToString($resultAR), 0);
        }

        return $resultAR;
    }


    public function Update_System_Data()
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if ($DebugActive === true) {
            $time_start_code = microtime(true);
        }

        $resultAR = array();

        if ($this->ReadPropertyBoolean('show_devicecomponentinformation') === true) {
            if ($this->GetBuffer('feature_preheater') !== '1') {
                $resultAR['OperatingHoursPreheater'] = $this->System_OperatingHours_Preheater_Get();
            }
            if ($this->GetBuffer('feature_afterheater') !== '1') {
                $resultAR['OperatingHoursAfterheater'] = $this->System_OperatingHours_Afterheater_Get();
            }
            $resultAR['OperatingHoursExtractAirFan'] = $this->System_OperatingHours_ExhaustAirFan_Get();
            $resultAR['OperatingHoursSupplyAirFan'] = $this->System_OperatingHours_SupplyAirFan_Get();
        }
        if ($this->ReadPropertyBoolean('show_devicesysteminformation') === true) {
            $resultAR['Messages']['DataExchange'] = $this->System_Messages_DataExchange_Get();
            $resultAR['Messages']['Error'] = $this->System_Messages_Error_Get();
            $resultAR['Messages']['ErrourCount'] = $this->System_Messages_ErrorCount_Get();
            $resultAR['Messages']['Info'] = $this->System_Messages_Info_Get();
            $resultAR['Messages']['InfoCount'] = $this->System_Messages_InfoCount_Get();
            $resultAR['Messages']['Status'] = $this->System_Messages_Status_Get();
            $resultAR['Messages']['Warning'] = $this->System_Messages_Warning_Get();
            $resultAR['Messages']['WarningCount'] = $this->System_Messages_WarningCount_Get();
            $resultAR['OperatingHoursSystem'] = $this->System_OperatingHours_System_Get();
            $resultAR['ProductionCode'] = $this->System_ProductionCode_Get();
            $resultAR['SecurityNumber'] = $this->System_SecurityNumber_Get();
            $resultAR['SensorControlSleepMode']['State'] = $this->System_SensorControlSleepMode_Get();
            $resultAR['SensorControlSleepMode']['From'] = $this->System_SensorControlSleepModeFROM_Get();
            $resultAR['SensorControlSleepMode']['To'] = $this->System_SensorControlSleepModeTO_Get();
            $resultAR['SerialNumber'] = $this->System_SerialNumber_Get();
            $resultAR['SoftwareVersion'] = $this->System_SoftwareVersion_Get();
            $resultAR['Type'] = $this->System_Type_Get();
        }
        if ($this->ReadPropertyBoolean('show_devicesystemsettings') === true) {
            $resultAR['AUTOatMidnight'] = $this->System_AUTOatMidnight_Get();
            $resultAR['CloudSync'] = $this->System_CloudSync_Get();
            $resultAR['Modbus'] = $this->System_Modbus_Get();
            $resultAR['SoftwareUpdateAutomatic'] = $this->System_SoftwareUpdateAutomatic_Get();
        }
        $resultAR['Date'] = $this->System_Date_Get();
        $resultAR['DateFormat'] = $this->System_DateFormat_Get();
        $resultAR['DaylightSavingTimeMode'] = $this->System_DaylightSavingTimeMode_Get();
        $resultAR['MACaddress'] = $this->System_MACAddress_Get();
        $resultAR['TimezoneGMT'] = $this->System_TimezoneGMT_Get();

        ksort($resultAR);

        if ($DebugActive === true) {
            $duration_code = round(microtime(true) - $time_start_code, 2);
            $this->SendDebug(__FUNCTION__, 'DURATION = ' . $duration_code . ' seconds', 0);
            $this->SendDebug(__FUNCTION__, 'resultAR = ' . $this->DataToString($resultAR), 0);
        }

        return $resultAR;
    }


    public function VOCControl_Get()
    {
        $result = $this->FunctionHelperGET('v00040', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('VOCControl', $result);
        }

        return $result;
    }


    // 0 = aus, 1 = stufig, 2 = stufenlos
    public function VOCControl_Set(int $value)
    {
        $vID = 'v00040';

        if (($value >= 0) || ($value <= 2)) {
            $result = $this->FunctionHelperSET($vID, $value);
            $this->SetValue_IfDifferent('VOCControl', $value);
            return $result;
        }

        return false;
    }


    // number = 1 bis 8
    public function VOCSensor_Get(int $number)
    {
        if ($number === 1) {
            $vID1 = 'v00136';
            $vID2 = 'v01091';
        } elseif (($number > 1) && ($number <= 8)) {
            $vID1x = 135 + $number;
            $vID1 = 'v00' . $vID1x;
            $vID2x = 1090 + $number;
            $vID2 = 'v0' . $vID2x;
        } else {
            $this->SendDebug(__FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Invalid sensor number') . ' // ' . $this->Translate('Number') . ' = ' . $number, 0, KL_ERROR);
            return false;
        }

        if ($this->GetBuffer($vID1) === '1') {
            $resultAR = array();

            $dataTEXT = @urldecode($this->Map_ID_to_VA_DataAll($vID2));
            if ($dataTEXT !== false) {
                $resultAR['Description'] = $dataTEXT;
            }

            $dataVOC = $this->FunctionHelperGET($vID1, __FUNCTION__, true);
            if ($dataVOC !== NULL) {
                $resultAR['VOC_ppm'] = (int)$dataVOC;
                $this->SetValue_IfDifferent('SensorVOC_' . $number, $resultAR['VOC_ppm']);
            }

            if (@array_key_exists('Description', $resultAR) === false) {
                $resultAR['Description'] = false;
            }
            if (@array_key_exists('VOC_ppm', $resultAR) === true) {
                return $resultAR;
            }
        }

        return false;
    }


    public function VOCSensors_All_Get()
    {
        $resultAR = array();
        for ($i = 1; $i <= 8; $i++) {
            $resultAR[$i] = $this->VOCSensor_Get($i);
        }

        return $resultAR;
    }


    public function WeekProgram_Get()
    {
        $result = $this->FunctionHelperGET('v00901', __FUNCTION__, true);

        if ($result !== NULL) {
            $result = (int)$result;

            $this->SetValue_IfDifferent('WeekProgram', $result);
        }

        return $result;
    }


    // 0 = Standard 1, 1 = Standard 2, 2 = Standard 3, 3 = Benutzerdefiniert 1, 4 = Benutzerdefiniert 2, 5 = Aus
    public function WeekProgram_Set(int $value)
    {
        $vID = 'v00901';

        if (($value >= 0) || ($value <= 5)) {
            $result = $this->FunctionHelperSET($vID, $value);
            $this->SetValue_IfDifferent('WeekProgram', $value);
            return $result;
        }

        return false;
    }
}

?>