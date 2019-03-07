<?php

trait HelperDebug
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
        if (is_object($messageText)) {
            foreach ($messageText as $Key => $DebugData) {
                parent::SendDebug($messageTitle . ':' . $Key, $DebugData, 0);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage('HELIOS // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage('HELIOS // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }

            }
        } elseif (is_array($messageText)) {
            foreach ($messageText as $Key => $DebugData) {
                parent::SendDebug($messageTitle . ':' . $Key, $DebugData, 0);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage('HELIOS // ' . $messageTitle . ':' . $Key . ' // ' . $DebugData, $logMessageType);
                    } else {
                        IPS_LogMessage('HELIOS // ' . $messageTitle . ':' . $Key, $DebugData);
                    }
                }
            }
        } elseif (is_bool($messageText)) {
            parent::SendDebug($messageTitle, $this->DataToString($messageText), 0);

            if ($logMessageType !== 99) {
                if (IPS_GetKernelVersion() >= 5.0) {
                    $this->LogMessage('HELIOS // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                } else {
                    IPS_LogMessage('HELIOS // ' . $messageTitle, $messageText);
                }
            }
        } else {
            if (IPS_GetKernelRunlevel() === KR_READY) {
                parent::SendDebug($messageTitle, $this->DataToString($messageText), (int)$messageFormat);

                if ($logMessageType !== 99) {
                    if (IPS_GetKernelVersion() >= 5.0) {
                        $this->LogMessage('HELIOS // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        IPS_LogMessage('HELIOS // ' . $messageTitle, $messageText);
                    }
                }
            } else {
                if (IPS_GetKernelVersion() >= 5.0) {
                    if ($logMessageType !== 99) {
                        $this->LogMessage('HELIOS // ' . $messageTitle . ' // ' . $messageText, $logMessageType);
                    } else {
                        $this->LogMessage('DEBUG // ' . $messageTitle . ' // ' . $messageText, KL_MESSAGE);
                    }
                } else {
                    IPS_LogMessage('HELIOS // ' . $messageTitle, $messageText);
                }
            }
        }
    }
}