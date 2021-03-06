<?php

/*
 * @package       Helios
 * @version       1.0
 * @file          helios_helper_buffer.php (This file is part of IP-Symcon module "Helios")
 * @author        Christoph Bach <info@bayaro.net>
 * @link          https://www.bayaro.net
 * @copyright     2019 Christoph Bach
 * @license       https://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License v3.0 only
 *
 * This file is part of IP-Symcon module "Helios".
 *
 * This module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, version 3.
 *
 * This module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this module. If not, see <http://www.gnu.org/licenses/>.
 */

trait HeliosHelperBuffer
{

    /**
     * GetBufferX (extends the function "GetBuffer" by the possibility to read large strings, arrays, ... also from multi buffers)
     *
     * @param $BufferName
     * @return bool|mixed
     */
    private function GetBufferX($BufferName)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if (strpos($BufferName, 'MultiBuffer_') === 0) {

            if (IPS_GetKernelVersion() >= 5.1) {
                $data = $this->ReadAttributeString($BufferName);
                if ($DebugActive === true) {
                    $this->SendDebug(__FUNCTION__, 'DEBUG // ReadAttributeString // BufferName = ' . $BufferName . ' // AttributeData = ' . $data, 0);
                }
                if ($data !== '') {
                    return @json_decode($data, true);
                }
            }

            $Buffer_Keys = @json_decode($this->GetBuffer('List_' . $BufferName), true); // get info from special list-buffer, how many parts this multi-buffer has
            if ($Buffer_Keys !== NULL) {
                $Buffer_Keys_AR = explode(',', $Buffer_Keys);
                $BufferParts = '';
                foreach ($Buffer_Keys_AR as $BufferIndex) {
                    $BufferParts .= $this->GetBuffer($BufferName . '_' . $BufferIndex);
                }

                if ($DebugActive === true) {
                    $this->SendDebug(__FUNCTION__, 'DEBUG // BufferName = ' . $BufferName . ' // BufferValue = ' . $BufferParts, 0);
                }

                return @json_decode($BufferParts, true);
            }

            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'DEBUG // BufferName = ' . $BufferName . ' // BufferValue = EMPTY', 0);
            }

            return false;
        }

        $BufferValue = $this->GetBuffer($BufferName);
        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'DEBUG // BufferName = ' . $BufferName . ' // BufferValue = ' . $BufferValue, 0);
        }

        return @json_decode($BufferValue, true);  // returns NULL if buffer does not exist
    }


    /**
     * SetBufferX (extends the function "SetBuffer" by the possibility to split and save large strings, arrays, ... into several buffers)
     *
     * @param $BufferName
     * @param $BufferData
     * @return mixed
     */
    private function SetBufferX($BufferName, $BufferData)
    {
        $DebugActive = $this->ReadPropertyBoolean('debug');

        if (strpos($BufferName, 'MultiBuffer_') === 0) {
            if (IPS_GetKernelVersion() >= 5.1) {
                if ($DebugActive === true) {
                    $this->SendDebug(__FUNCTION__, 'WriteAttributeString // BufferName = ' . $BufferName . ' // BufferData = ' . json_encode($BufferData), 0);
                }
                return $this->WriteAttributeString($BufferName, json_encode($BufferData));
            }
            $BufferNew_AR = str_split(json_encode($BufferData), 8000);  // split buffer data into 8kb parts - ips buffer soft limit is 8kb
            $BufferList = '';
            foreach ($BufferNew_AR as $BufferNew_Index => $BufferNew_Part) {
                $BufferList .= $BufferNew_Index . ',';

                $this->SetBuffer($BufferName . '_' . $BufferNew_Index, $BufferNew_Part);  // put part in buffer
                if ($DebugActive === true) {
                    $this->SendDebug(__FUNCTION__, 'BufferName = ' . $BufferName . '_' . $BufferNew_Index . ' // BufferValuePart = ' . $BufferNew_Part, 0);
                }
            }

            $result = $this->SetBuffer('List_' . $BufferName, json_encode(substr($BufferList, 0, -1))); // set new buffer list

            if ($DebugActive === true) {
                $this->SendDebug(__FUNCTION__, 'BufferName = List_' . $BufferName . ' // BufferList = ' . substr($BufferList, 0, -1), 0);
            }

            return $result;
        }

        $result = $this->SetBuffer($BufferName, json_encode($BufferData));

        if ($DebugActive === true) {
            $this->SendDebug(__FUNCTION__, 'BufferName = ' . $BufferName . ' // BufferValue = ' . json_encode($BufferData), 0);
        }

        return $result;
    }
}