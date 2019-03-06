<?php

if(!defined('IPS_BASE')) {
    define('IPS_BASE', 10000);
}
if(!defined('IPS_KERNELSTARTED')) {
    define('IPS_KERNELSTARTED', IPS_BASE + 1);
}
if(!defined('IPS_KERNELSHUTDOWN')) {
    define('IPS_KERNELSHUTDOWN', IPS_BASE + 2);
}

if(!defined('IPS_KERNELMESSAGE')) {
    define('IPS_KERNELMESSAGE', IPS_BASE + 100);
}
if(!defined('KR_CREATE')) {
    define('KR_CREATE', IPS_KERNELMESSAGE + 1);
}
if(!defined('KR_INIT')) {
    define('KR_INIT', IPS_KERNELMESSAGE + 2);
}
if(!defined('KR_READY')) {
    define('KR_READY', IPS_KERNELMESSAGE + 3);
}
if(!defined('KR_UNINIT')) {
    define('KR_UNINIT', IPS_KERNELMESSAGE + 4);
}
if(!defined('KR_SHUTDOWN')) {
    define('KR_SHUTDOWN', IPS_KERNELMESSAGE + 5);
}

if(!defined('IPS_LOGMESSAGE')) {
    define('IPS_LOGMESSAGE', IPS_BASE + 200);
}
if(!defined('KL_MESSAGE')) {
    define('KL_MESSAGE', IPS_LOGMESSAGE + 1);
}
if(!defined('KL_NOTIFY')) {
    define('KL_NOTIFY', IPS_LOGMESSAGE + 3);
}
if(!defined('KL_WARNING')) {
    define('KL_WARNING', IPS_LOGMESSAGE + 4);
}
if(!defined('KL_ERROR')) {
    define('KL_ERROR', IPS_LOGMESSAGE + 5);
}
if(!defined('KL_DEBUG')) {
    define('KL_DEBUG', IPS_LOGMESSAGE + 6);
}

if(!defined('IPS_MODULEMESSAGE')) {
    define('IPS_MODULEMESSAGE', IPS_BASE + 300);
}
if(!defined('ML_LOAD')) {
    define('ML_LOAD', IPS_MODULEMESSAGE + 1);
}
if(!defined('ML_UNLOAD')) {
    define('ML_UNLOAD', IPS_MODULEMESSAGE + 2);
}

if(!defined('IPS_OBJECTMESSAGE')) {
    define('IPS_OBJECTMESSAGE', IPS_BASE + 400);
}
if(!defined('OM_REGISTER')) {
    define('OM_REGISTER', IPS_OBJECTMESSAGE + 1);
}
if(!defined('OM_UNREGISTER')) {
    define('OM_UNREGISTER', IPS_OBJECTMESSAGE + 2);
}
if(!defined('OM_CHANGEPARENT')) {
    define('OM_CHANGEPARENT', IPS_OBJECTMESSAGE + 3);
}
if(!defined('OM_CHANGENAME')) {
    define('OM_CHANGENAME', IPS_OBJECTMESSAGE + 4);
}
if(!defined('OM_CHANGEINFO')) {
    define('OM_CHANGEINFO', IPS_OBJECTMESSAGE + 5);
}
if(!defined('OM_CHANGETYPE')) {
    define('OM_CHANGETYPE', IPS_OBJECTMESSAGE + 6);
}
if(!defined('OM_CHANGESUMMARY')) {
    define('OM_CHANGESUMMARY', IPS_OBJECTMESSAGE + 7);
}
if(!defined('OM_CHANGEPOSITION')) {
    define('OM_CHANGEPOSITION', IPS_OBJECTMESSAGE + 8);
}
if(!defined('OM_CHANGEREADONLY')) {
    define('OM_CHANGEREADONLY', IPS_OBJECTMESSAGE + 9);
}
if(!defined('OM_CHANGEHIDDEN')) {
    define('OM_CHANGEHIDDEN', IPS_OBJECTMESSAGE + 10);
}
if(!defined('OM_CHANGEICON')) {
    define('OM_CHANGEICON', IPS_OBJECTMESSAGE + 11);
}
if(!defined('OM_CHILDADDED')) {
    define('OM_CHILDADDED', IPS_OBJECTMESSAGE + 12);
}
if(!defined('OM_CHILDREMOVED')) {
    define('OM_CHILDREMOVED', IPS_OBJECTMESSAGE + 13);
}
if(!defined('OM_CHANGEIDENT')) {
    define('OM_CHANGEIDENT', IPS_OBJECTMESSAGE + 14);
}
if(!defined('OM_CHANGEDISABLED')) {
    define('OM_CHANGEDISABLED', IPS_OBJECTMESSAGE + 15);
}

if(!defined('IPS_INSTANCEMESSAGE')) {
    define('IPS_INSTANCEMESSAGE', IPS_BASE + 500);
}
if(!defined('IM_CREATE')) {
    define('IM_CREATE', IPS_INSTANCEMESSAGE + 1);
}
if(!defined('IM_DELETE')) {
    define('IM_DELETE', IPS_INSTANCEMESSAGE + 2);
}
if(!defined('IM_CONNECT')) {
    define('IM_CONNECT', IPS_INSTANCEMESSAGE + 3);
}
if(!defined('IM_DISCONNECT')) {
    define('IM_DISCONNECT', IPS_INSTANCEMESSAGE + 4);
}
if(!defined('IM_CHANGESTATUS')) {
    define('IM_CHANGESTATUS', IPS_INSTANCEMESSAGE + 5);
}
if(!defined('IM_CHANGESETTINGS')) {
    define('IM_CHANGESETTINGS', IPS_INSTANCEMESSAGE + 6);
}

if(!defined('IPS_SEARCHMESSAGE')) {
    define('IPS_SEARCHMESSAGE', IPS_BASE + 510);
}
if(!defined('IM_SEARCHSTART')) {
    define('IM_SEARCHSTART', IPS_SEARCHMESSAGE + 1);
}
if(!defined('IM_SEARCHSTOP')) {
    define('IM_SEARCHSTOP', IPS_SEARCHMESSAGE + 2);
}
if(!defined('IM_SEARCHUPDATE')) {
    define('IM_SEARCHUPDATE', IPS_SEARCHMESSAGE + 3);
}

if(!defined('IPS_VARIABLEMESSAGE')) {
    define('IPS_VARIABLEMESSAGE', IPS_BASE + 600);
}
if(!defined('VM_CREATE')) {
    define('VM_CREATE', IPS_VARIABLEMESSAGE + 1);
}
if(!defined('VM_DELETE')) {
    define('VM_DELETE', IPS_VARIABLEMESSAGE + 2);
}
if(!defined('VM_UPDATE')) {
    define('VM_UPDATE', IPS_VARIABLEMESSAGE + 3);
}
if(!defined('VM_CHANGEPROFILENAME')) {
    define('VM_CHANGEPROFILENAME', IPS_VARIABLEMESSAGE + 4);
}
if(!defined('VM_CHANGEPROFILEACTION')) {
    define('VM_CHANGEPROFILEACTION', IPS_VARIABLEMESSAGE + 5);
}

if(!defined('IPS_SCRIPTMESSAGE')) {
    define('IPS_SCRIPTMESSAGE', IPS_BASE + 700);
}
if(!defined('SM_CREATE')) {
    define('SM_CREATE', IPS_SCRIPTMESSAGE + 1);
}
if(!defined('SM_DELETE')) {
    define('SM_DELETE', IPS_SCRIPTMESSAGE + 2);
}
if(!defined('SM_CHANGEFILE')) {
    define('SM_CHANGEFILE', IPS_SCRIPTMESSAGE + 3);
}
if(!defined('SM_BROKEN')) {
    define('SM_BROKEN', IPS_SCRIPTMESSAGE + 4);
}

if(!defined('IPS_EVENTMESSAGE')) {
    define('IPS_EVENTMESSAGE', IPS_BASE + 800);
}
if(!defined('EM_CREATE')) {
    define('EM_CREATE', IPS_EVENTMESSAGE + 1);
}
if(!defined('EM_DELETE')) {
    define('EM_DELETE', IPS_EVENTMESSAGE + 2);
}
if(!defined('EM_UPDATE')) {
    define('EM_UPDATE', IPS_EVENTMESSAGE + 3);
}
if(!defined('EM_CHANGEACTIVE')) {
    define('EM_CHANGEACTIVE', IPS_EVENTMESSAGE + 4);
}
if(!defined('EM_CHANGELIMIT')) {
    define('EM_CHANGELIMIT', IPS_EVENTMESSAGE + 5);
}
if(!defined('EM_CHANGESCRIPT')) {
    define('EM_CHANGESCRIPT', IPS_EVENTMESSAGE + 6);
}
if(!defined('EM_CHANGETRIGGER')) {
    define('EM_CHANGETRIGGER', IPS_EVENTMESSAGE + 7);
}
if(!defined('EM_CHANGETRIGGERVALUE')) {
    define('EM_CHANGETRIGGERVALUE', IPS_EVENTMESSAGE + 8);
}
if(!defined('EM_CHANGETRIGGEREXECUTION')) {
    define('EM_CHANGETRIGGEREXECUTION', IPS_EVENTMESSAGE + 9);
}
if(!defined('EM_CHANGECYCLIC')) {
    define('EM_CHANGECYCLIC', IPS_EVENTMESSAGE + 10);
}
if(!defined('EM_CHANGECYCLICDATEFROM')) {
    define('EM_CHANGECYCLICDATEFROM', IPS_EVENTMESSAGE + 11);
}
if(!defined('EM_CHANGECYCLICDATETO')) {
    define('EM_CHANGECYCLICDATETO', IPS_EVENTMESSAGE + 12);
}
if(!defined('EM_CHANGECYCLICTIMEFROM')) {
    define('EM_CHANGECYCLICTIMEFROM', IPS_EVENTMESSAGE + 13);
}
if(!defined('EM_CHANGECYCLICTIMETO')) {
    define('EM_CHANGECYCLICTIMETO', IPS_EVENTMESSAGE + 14);
}
if(!defined('EM_ADDSCHEDULEACTION')) {
    define('EM_ADDSCHEDULEACTION', IPS_EVENTMESSAGE + 15);
}
if(!defined('EM_REMOVESCHEDULEACTION')) {
    define('EM_REMOVESCHEDULEACTION', IPS_EVENTMESSAGE + 16);
}
if(!defined('EM_CHANGESCHEDULEACTION')) {
    define('EM_CHANGESCHEDULEACTION', IPS_EVENTMESSAGE + 17);
}
if(!defined('EM_ADDSCHEDULEGROUP')) {
    define('EM_ADDSCHEDULEGROUP', IPS_EVENTMESSAGE + 18);
}
if(!defined('EM_REMOVESCHEDULEGROUP')) {
    define('EM_REMOVESCHEDULEGROUP', IPS_EVENTMESSAGE + 19);
}
if(!defined('EM_CHANGESCHEDULEGROUP')) {
    define('EM_CHANGESCHEDULEGROUP', IPS_EVENTMESSAGE + 20);
}
if(!defined('EM_ADDSCHEDULEGROUPPOINT')) {
    define('EM_ADDSCHEDULEGROUPPOINT', IPS_EVENTMESSAGE + 21);
}
if(!defined('EM_REMOVESCHEDULEGROUPPOINT')) {
    define('EM_REMOVESCHEDULEGROUPPOINT', IPS_EVENTMESSAGE + 22);
}
if(!defined('EM_CHANGESCHEDULEGROUPPOINT')) {
    define('EM_CHANGESCHEDULEGROUPPOINT', IPS_EVENTMESSAGE + 23);
}

if(!defined('IPS_MEDIAMESSAGE')) {
    define('IPS_MEDIAMESSAGE', IPS_BASE + 900);
}
if(!defined('MM_CREATE')) {
    define('MM_CREATE', IPS_MEDIAMESSAGE + 1);
}
if(!defined('MM_DELETE')) {
    define('MM_DELETE', IPS_MEDIAMESSAGE + 2);
}
if(!defined('MM_CHANGEFILE')) {
    define('MM_CHANGEFILE', IPS_MEDIAMESSAGE + 3);
}
if(!defined('MM_AVAILABLE')) {
    define('MM_AVAILABLE', IPS_MEDIAMESSAGE + 4);
}
if(!defined('MM_UPDATE')) {
    define('MM_UPDATE', IPS_MEDIAMESSAGE + 5);
}
if(!defined('MM_CHANGECACHED')) {
    define('MM_CHANGECACHED', IPS_MEDIAMESSAGE + 6);
}

if(!defined('IPS_LINKMESSAGE')) {
    define('IPS_LINKMESSAGE', IPS_BASE + 1000);
}
if(!defined('LM_CREATE')) {
    define('LM_CREATE', IPS_LINKMESSAGE + 1);
}
if(!defined('LM_DELETE')) {
    define('LM_DELETE', IPS_LINKMESSAGE + 2);
}
if(!defined('LM_CHANGETARGET')) {
    define('LM_CHANGETARGET', IPS_LINKMESSAGE + 3);
}

if(!defined('IPS_FLOWMESSAGE')) {
    define('IPS_FLOWMESSAGE', IPS_BASE + 1100);
}
if(!defined('FM_CONNECT')) {
    define('FM_CONNECT', IPS_FLOWMESSAGE + 1);
}
if(!defined('FM_DISCONNECT')) {
    define('FM_DISCONNECT', IPS_FLOWMESSAGE + 2);
}

if(!defined('IPS_ENGINEMESSAGE')) {
    define('IPS_ENGINEMESSAGE', IPS_BASE + 1200);
}
if(!defined('SE_UPDATE')) {
    define('SE_UPDATE', IPS_ENGINEMESSAGE + 1);
}
if(!defined('SE_EXECUTE')) {
    define('SE_EXECUTE', IPS_ENGINEMESSAGE + 2);
}
if(!defined('SE_RUNNING')) {
    define('SE_RUNNING', IPS_ENGINEMESSAGE + 3);
}

if(!defined('IPS_PROFILEMESSAGE')) {
    define('IPS_PROFILEMESSAGE', IPS_BASE + 1300);
}
if(!defined('PM_CREATE')) {
    define('PM_CREATE', IPS_PROFILEMESSAGE + 1);
}
if(!defined('PM_DELETE')) {
    define('PM_DELETE', IPS_PROFILEMESSAGE + 2);
}
if(!defined('PM_CHANGETEXT')) {
    define('PM_CHANGETEXT', IPS_PROFILEMESSAGE + 3);
}
if(!defined('PM_CHANGEVALUES')) {
    define('PM_CHANGEVALUES', IPS_PROFILEMESSAGE + 4);
}
if(!defined('PM_CHANGEDIGITS')) {
    define('PM_CHANGEDIGITS', IPS_PROFILEMESSAGE + 5);
}
if(!defined('PM_CHANGEICON')) {
    define('PM_CHANGEICON', IPS_PROFILEMESSAGE + 6);
}
if(!defined('PM_ASSOCIATIONADDED')) {
    define('PM_ASSOCIATIONADDED', IPS_PROFILEMESSAGE + 7);
}
if(!defined('PM_ASSOCIATIONREMOVED')) {
    define('PM_ASSOCIATIONREMOVED', IPS_PROFILEMESSAGE + 8);
}
if(!defined('PM_ASSOCIATIONCHANGED')) {
    define('PM_ASSOCIATIONCHANGED', IPS_PROFILEMESSAGE + 9);
}

if(!defined('IPS_TIMERMESSAGE')) {
    define('IPS_TIMERMESSAGE', IPS_BASE + 1400);
}
if(!defined('TM_REGISTER')) {
    define('TM_REGISTER', IPS_TIMERMESSAGE + 1);
}
if(!defined('TM_UNREGISTER')) {
    define('TM_UNREGISTER', IPS_TIMERMESSAGE + 2);
}
if(!defined('TM_CHANGEINTERVAL')) {
    define('TM_CHANGEINTERVAL', IPS_TIMERMESSAGE + 3);
}
if(!defined('TM_CHANGEPROGRESS')) {
    define('TM_CHANGEPROGRESS', IPS_TIMERMESSAGE + 4);
}
if(!defined('TM_MESSAGE')) {
    define('TM_MESSAGE', IPS_TIMERMESSAGE + 5);
}


if(!defined('IS_SBASE')) {
    define('IS_SBASE', 100);
}
if(!defined('IS_CREATING')) {
    define('IS_CREATING', IS_SBASE + 1);
}
if(!defined('IS_ACTIVE')) {
    define('IS_ACTIVE', IS_SBASE + 2);
}
if(!defined('IS_DELETING')) {
    define('IS_DELETING', IS_SBASE + 3);
}
if(!defined('IS_INACTIVE')) {
    define('IS_INACTIVE', IS_SBASE + 4);
}

if(!defined('IS_EBASE')) {
    define('IS_EBASE', 200);
}
if(!defined('IS_NOTCREATED')) {
    define('IS_NOTCREATED', IS_EBASE + 1);
}

?>