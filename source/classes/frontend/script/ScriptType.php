<?php

namespace Plugin\dh_bonuspunkte\source\classes\frontend\script;

/**
 * This enumeration is used for choosing a type of script and is
 * mainly used by the ScriptManager class
 */
enum ScriptType
{
    // Interactive JavaScript, Stylesheets bundled with WebPack
    case WebpackInline;
    // Only debug messages in the javascript console
    case DebugMessages;
}