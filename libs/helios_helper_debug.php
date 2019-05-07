<?php

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
                        $this->LogMessage($deviceIP.' // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP.' // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }

            }
        } elseif (is_array($messageText)) {
            foreach ($messageText as $Key => $DebugData) {
                parent::SendDebug($messageTitle . ':' . $Key, $DebugData, 0);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage($deviceIP.' // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP.' // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }
            }
        } elseif (is_bool($messageText)) {
            parent::SendDebug($messageTitle, $this->DataToString($messageText), 0);

            if ($logMessageType !== 99) {
                if (IPS_GetKernelVersion() >= 5.0) {
                    $this->LogMessage($deviceIP.' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                } else {
                    IPS_LogMessage($deviceIP.' // ' . $messageTitle, $messageText);
                }
            }
        } else {
            if (IPS_GetKernelRunlevel() === KR_READY) {
                parent::SendDebug($messageTitle, $this->DataToString($messageText), (int)$messageFormat);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage($deviceIP.' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        IPS_LogMessage($deviceIP.' // ' . $messageTitle, $messageText);
                    }
                }
            } else {
                if (IPS_GetKernelVersion() >= 5.0) {
                    if ($logMessageType !== 99) {
                        $this->LogMessage($deviceIP.' // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        $this->LogMessage($deviceIP.' // ' . $messageTitle . ' // ' . $messageText, KL_MESSAGE);
                    }
                } else {
                    IPS_LogMessage($deviceIP.' // ' . $messageTitle, $messageText);
                }
            }
        }
    }
}