<?php

namespace Plugin\dh_bonuspunkte\source\classes\helper;

use JTL\Plugin\Data\Config;
use JTL\Plugin\PluginInterface;

class PluginSettingsAccessor
{
    /**
     * Get the plugin interface from the global variable
     * @return PluginInterface
     */
    private static function getPluginInterface(): PluginInterface
    {
        return PluginInterfaceAccessor::getPluginInterface();
    }

    /**
     * Get the plugin settings from the plugin interface
     * @return Config
     */
    private static function getPluginConfig(): Config
    {
        return static::getPluginInterface()->getConfig();
    }

    /**
     * Get a plugin setting by its name
     * @param string $keyName
     * @return mixed
     */
    private static function getPluginConfigValue(string $keyName): mixed
    {
        return static::getPluginConfig()->getValue($keyName);
    }

    /**
     * Get the plugin setting (checkbox) state, if enabled or not
     * @param string $keyName
     * @return bool True if enabled ("on")
     */
    private static function isSettingCheckboxOn(string $keyName): bool
    {
        return static::getPluginConfigValue($keyName) ?? "off" == "on";
    }

    /**
     * Get the amount of points as reward for a recurring visit
     * @return int
     */
    public static function getRewardPerVisitInPoints(): int
    {
        return (int) static::getPluginConfigValue("rewardPerVisit") ?? 0;
    }

    /**
     * Get the cooldown for gaining points again for visiting the shop
     * @return string YEAR|MONTH|WEEK|DAY
     */
    public static function getRewardPerVisitCooldownOption(): string
    {
        return static::getPluginConfigValue("rewardPerVisitCooldown") ?? "WEEK";
    }

    /**
     * Get if the user is eligible to gain points for a recurring visit
     * @return bool
     */
    public static function getRewardPerVisitIsEnabled(): bool
    {
        return static::isSettingCheckboxOn("enableRewardVisit");
    }

    /**
     * Get the amount of points as reward for a registration
     * @return int
     */
    public static function getRewardPerRegistrationInPoints(): int
    {
        return (int) static::getPluginConfigValue("rewardPerRegister") ?? 0;
    }

    /**
     * Get if the user is eligible for gaining points for a registration
     * @return bool
     */
    public static function getRewardPerRegistrationIsEnabled(): bool
    {
        return static::isSettingCheckboxOn("enableRewardRegister");
    }

    /**
     * Get if the user is eligible for gaining points for a login in his account
     * @return bool
     */
    public static function getRewardPerLoginIsEnabled(): bool
    {
        return static::isSettingCheckboxOn("enableRewardLogin");
    }

    /**
     * Get the cooldown for gaining points again for login into the account
     * @return string YEAR|MONTH|WEEK|DAY
     */
    public static function getRewardPerLoginCooldownOption(): string
    {
        return static::getPluginConfigValue("rewardPerLoginCooldown") ?? "WEEK";
    }

    /**
     * Get the amount of points (reward) for a login into the account after the cooldown
     * @return int
     */
    public static function getRewardPerLoginInPoints(): int
    {
        return (int) static::getPluginConfigValue("rewardPerLogin") ?? 0;
    }

    /**
     * Get the reward for each bought article (by default)
     * @return int
     */
    public static function getRewardPerArticleByDefault(): int
    {
        return (int) static::getPluginConfigValue("rewardPerArticle") ?? 0;
    }

    /**
     * Get the reward for each unique bought article. In this case it is determined, if the article has
     * the same internal article id. If it's duplicated or more of 1n is in the cart, the amount above 1 will be ignored
     * @return int
     */
    public static function getRewardPerArticleOnceByDefault(): int
    {
        return (int) static::getPluginConfigValue("rewardPerArticleOnce") ?? 0;
    }

    /**
     * Get the reward for sales volume in euro. Each euro will be rewarded with n amount of points.
     * @return int
     */
    public static function getRewardPerValueEachEuroByDefault(): int
    {
        return (int) static::getPluginConfigValue("rewardPerEuro") ?? 0;
    }

    /**
     * Get if the calculation for sales volume will be done in net or gross prices
     * @return bool
     */
    public static function getRewardPerValueEachEuroInNetPrices(): bool
    {
        return static::isSettingCheckboxOn("calculateWithNetPrice");
    }

    /**
     * Get if the conversion from loyalty points to shop balance is enabled
     * @return bool
     */
    public static function getConversionToShopBalanceIsEnabled(): bool
    {
        return static::isSettingCheckboxOn("conversionToEuroEnabled");
    }

    /**
     * Get the rate from amount in points to one euro
     * @return int
     */
    public static function getConversionRateForEachEuroInPoints(): int
    {
        return (int) static::getPluginConfigValue("conversionToEuroEachPoint") ?? 100;
    }

    /**
     * Get the minimum amount of points for trade-in to shop balance
     * @return int
     */
    public static function getConversionMinimumPointsTradeIn(): int
    {
        return (int) static::getPluginConfigValue("conversionMinimumPointAmount") ?? 1000;
    }

    /**
     * This is the duration in days until the points are unlocked for the customer
     * @return int
     */
    public static function getRewardUnlockAfterDays(): int
    {
        return (int) static::getPluginConfigValue("rewardAfterThisDays") ?? 15;
    }
}