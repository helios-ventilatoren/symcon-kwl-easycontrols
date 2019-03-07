<?php

trait HelperVariables
{
    /**
     * RegisterObjectMedia (creating a boolean variable profile with given parameters)
     *
     * @param $objectName
     * @param $objectIdent
     * @param $objectType
     * @param $objectParent
     * @param $cached
     * @param $fileName
     * @param $position
     * @return bool
     */
    protected function RegisterObjectMedia($objectName, $objectIdent, $objectType, $objectParent, $cached, $fileName, $position = 0)
    {
        $ObjectID = @$this->GetIDForIdent($objectIdent);
        if ($ObjectID === false) {
            $ObjectID = IPS_CreateMedia($objectType);
            IPS_SetParent($ObjectID, $objectParent);
            IPS_SetIdent($ObjectID, $objectIdent);
            IPS_SetName($ObjectID, $objectName);
            IPS_SetPosition($ObjectID, $position);
            IPS_SetMediaCached($ObjectID, $cached);
            $ImageFile = IPS_GetKernelDir() . 'media' . DIRECTORY_SEPARATOR . $fileName;
            return IPS_SetMediaFile($ObjectID, $ImageFile, false);
        }

        return true;
    }


    /**
     * GetValue (get value of variable)
     *
     * @param $varIdent
     * @return bool
     */
    protected function GetValue($varIdent)
    {
        if (IPS_GetKernelVersion() >= 5) {
            return parent::GetValue($varIdent);
        }

        return GetValue($this->GetIDForIdent($varIdent));
    }


    protected function RegisterReference($objectID)
    {
        $this->LogMessage('RegisterReference 1 // objectID = ' .$objectID, KL_NOTIFY);

        if (IPS_GetKernelVersion() >= 5.1) {
            $this->LogMessage('RegisterReference 2 // objectID = ' .$objectID, KL_NOTIFY);
            parent::RegisterReference($objectID);
        }
    }


    protected function RegisterReference_Property($propertyName)
    {
        $objectID = $this->ReadPropertyInteger($propertyName);
        $this->LogMessage("RegisterReference_Property 1 // propertyName = ".$propertyName, KL_NOTIFY);

        if ($objectID > 0) {
            $this->LogMessage("RegisterReference_Property 2 // propertyName = ".$propertyName, KL_NOTIFY);
            $this->RegisterReference($objectID);
        }

        return true;
    }


    /**
     * SetValue (set variable to new value, no matter whether the new value is the same or different)
     *
     * @param string $varIdent
     * @param $value
     * @return bool
     */
    protected function SetValue($varIdent, $value)
    {
        $VarID = @$this->GetIDForIdent($varIdent);

        if (IPS_GetKernelVersion() >= 5) {
            if ($VarID > 0) {
                switch (IPS_GetVariable($VarID)['VariableType']) {
                    case 0:
                        parent::SetValue($varIdent, (bool)$value);
                        break;

                    case 1:
                        parent::SetValue($varIdent, (int)$value);
                        break;

                    case 2:
                        parent::SetValue($varIdent, (float)$value);
                        break;

                    case 3:
                        parent::SetValue($varIdent, (string)$value);
                        break;
                }
                return true;
            }
        } else {
            if ($VarID > 0) {
                switch (IPS_GetVariable($VarID)['VariableType']) {
                    case 0:
                        SetValue($VarID, (bool)$value);
                        break;

                    case 1:
                        SetValue($VarID, (int)$value);
                        break;

                    case 2:
                        SetValue($VarID, (float)$value);
                        break;

                    case 3:
                        SetValue($VarID, (string)$value);
                        break;
                }
                return true;
            }
        }

        return false;
    }


    /**
     * SetValue_IfDifferent (set variable to new value, if the value is different)
     *
     * @param $varIdent
     * @param $value
     * @return bool
     */
    protected function SetValue_IfDifferent($varIdent, $value)
    {
        $VarID = @$this->GetIDForIdent($varIdent);

        if ($VarID > 0) {
            if (IPS_VariableExists($VarID) === true) {
                if ($this->GetValue($varIdent) != $value) {
                    $this->SetValue($varIdent, $value);
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * SetValue_ToDefaultOnce (sets a variable once to a default value set by attribute)
     *
     * @param $varIdent
     * @param $defaultAttribute
     * @return bool
     */
    protected function SetValue_ToDefaultOnce($varIdent, $defaultAttribute)
    {
        if (IPS_GetKernelVersion() < 5.1) {
            if (IPS_GetKernelVersion() >= 5.0) {
                $this->LogMessage('HELIOS // ' . __FUNCTION__ . ' // ' . $this->Translate('INFO') . ' // ' . $this->Translate('Setting default values is only possible with IP-Symcon Version 5.1 and later'), KL_NOTIFY);
            } else {
                IPS_LogMessage('HELIOS // ' . __FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Setting default values is only possible with IP-Symcon Version 5.1 and later'));
            }
            return false;
        }

        if ($this->ReadAttributeBoolean($defaultAttribute . '_DONE') === true) {
            return false;
        }
        $this->WriteAttributeBoolean($defaultAttribute . '_DONE', true);

        $defaultValue = NULL;
        $defaultAttributeType = @IPS_GetVariable($this->GetIDForIdent($varIdent))['VariableType'];
        if ($defaultAttributeType === 0) {
            $defaultValue = $this->ReadAttributeBoolean($defaultAttribute);
        } elseif ($defaultAttributeType === 1) {
            $defaultValue = $this->ReadAttributeInteger($defaultAttribute);
        } elseif ($defaultAttributeType === 2) {
            $defaultValue = $this->ReadAttributeFloat($defaultAttribute);
        } elseif ($defaultAttributeType === 3) {
            $defaultValue = $this->ReadAttributeString($defaultAttribute);
        }

        if ($defaultValue === NULL) {
            if (IPS_GetKernelVersion() >= 5.0) {
                $this->LogMessage('HELIOS // ' . __FUNCTION__ . ' // ' . $this->Translate('ERROR') . ' // ' . $this->Translate('Default value for variable could not be determined'), KL_ERROR);
            } else {
                IPS_LogMessage('HELIOS // ' . __FUNCTION__, $this->Translate('ERROR') . ' // ' . $this->Translate('Default value for variable could not be determined'));
            }
            return false;
        }

        return $this->SetValue_IfDifferent($varIdent, $defaultValue);
    }


    protected function UnregisterReference($objectID)
    {
        if (method_exists('IPSModule', 'UnregisterReference ') === true) {
            parent::UnregisterReference($objectID);
        }
    }


    protected function UnregisterReferences()
    {
        if (method_exists($this, 'GetReferenceList')) {
            $refs = $this->GetReferenceList();
            foreach ($refs as $ref) {
                $this->UnregisterReference($ref);
            }
        }
    }


    /**
     * Variable_Register (register and create variable with some parameters)
     *
     * @param $varIdent
     * @param $varName
     * @param $varProfile
     * @param $varIcon
     * @param $varType
     * @param $enableAction
     * @param $hide
     * @param $position
     */
    protected function Variable_Register($varIdent, $varName, $varProfile, $varIcon, $varType, $enableAction, $hide = false, $position = false)
    {
        if ($position === false) {
            $positionX = 0;
        } else {
            $positionX = $position;
        }

        $varID = 0;
        switch ($varType) {
            case 0:
                $varID = $this->RegisterVariableBoolean($varIdent, $varName, $varProfile, $positionX);
                break;

            case 1:
                $varID = $this->RegisterVariableInteger($varIdent, $varName, $varProfile, $positionX);
                break;

            case 2:
                $varID = $this->RegisterVariableFloat($varIdent, $varName, $varProfile, $positionX);
                break;

            case 3:
                $varID = $this->RegisterVariableString($varIdent, $varName, $varProfile, $positionX);
                break;
        }

        if ($varID > 0) {

            if ($varIcon !== '') {
                IPS_SetIcon($varID, $varIcon);
            }

            if ($position !== false) {
                IPS_SetPosition($varID, $positionX);
            }

            IPS_SetHidden($varID, $hide);

            if ($enableAction === true) {
                $this->EnableAction($varIdent);
            }
        }
    }


    /**
     * Variable_Unregister (unregister and delete variable)
     *
     * @param $varIdent
     * @return bool
     */
    protected function Variable_Unregister($varIdent)
    {
        $VarID = @$this->GetIDForIdent($varIdent);
        if ($VarID > 0) {
            if (IPS_VariableExists($VarID) === false) {
                $this->SendDebug(__FUNCTION__, $this->Translate('INFO') . ' // ' . $this->Translate('Variable with ID ') . '"' . $VarID . '"' . $this->Translate(' does not exist'), 0);
                return false;
            }
            $this->UnregisterVariable($varIdent);

            return true;
        }

        return false;
    }
}