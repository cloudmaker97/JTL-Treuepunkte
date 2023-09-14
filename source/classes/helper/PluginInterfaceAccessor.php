<?php

namespace Plugin\dh_bonuspunkte\source\classes\helper;

use JTL\Plugin\PluginInterface;

class PluginInterfaceAccessor
{
    /**
     * Get the Plugin Interface from the global scoped variable, which is defined
     * in the Bootstrap for easier access without passing it through all objects.
     * It is important to mention, that this is not a good-practice technique, but it works.
     * Use it only, when you know what you do. Really. You don't want outer-scoped variables.
     * @expectedDeprecation
     * @return PluginInterface
     */
    public static function getPluginInterface(): PluginInterface {
        global $pluginInterfaceForDhBonuspoints;
        return $pluginInterfaceForDhBonuspoints;
    }
}