<?php

/*
 * @package       Helios
 * @version       1.0
 * @file          helios_helper_debug.php (This file is part of IP-Symcon module "Helios")
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

trait HeliosHelperDebug
{
    /**
     * SendDebug (extend "SendDebug" by further output options)
     *
     * @param string $messageTitle
     * @param mixed $messageText
     * @param int $messageFormat
     * @param int $logMessageType
     */
    protected function SendDebug($messageTitle, $messageText, $messageFormat, $logMessageType = 99)
    {
        $deviceIP = $this->ReadPropertyString('deviceip');

        if (is_object($messageText)) {
            foreach ($messageText as $Key => $DebugData) {
                parent::SendDebug($messageTitle . ':' . $Key, $DebugData, 0);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage($deviceIP . ' // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP . ' // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }

            }
        } elseif (is_array($messageText)) {
            foreach ($messageText as $Key => $DebugData) {
                parent::SendDebug($messageTitle . ':' . $Key, $DebugData, 0);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage($deviceIP . ' // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP . ' // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }
            }
        } elseif (is_bool($messageText)) {
            parent::SendDebug($messageTitle, $this->DataToString($messageText), 0);

            if ($logMessageType !== 99) {
                if (IPS_GetKernelVersion() >= 5.0) {
                    $this->LogMessage($deviceIP . ' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                } else {
                    IPS_LogMessage($deviceIP . ' // ' . $messageTitle, $messageText);
                }
            }
        } else {
            if (IPS_GetKernelRunlevel() === KR_READY) {
                parent::SendDebug($messageTitle, $this->DataToString($messageText), (int)$messageFormat);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage($deviceIP . ' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP . ' // ' . $messageTitle, $messageText);
                    }
                }
            } else {
                if (IPS_GetKernelVersion() >= 5.0) {
                    if ($logMessageType !== 99) {
                        $this->LogMessage($deviceIP . ' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        $this->LogMessage($deviceIP . ' // ' . $messageTitle . ' // ' . $messageText, KL_MESSAGE);
                    }
                } else {
                    IPS_LogMessage($deviceIP . ' // ' . $messageTitle, $messageText);
                }
            }
        }
    }
}