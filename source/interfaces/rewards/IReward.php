<?php

namespace Plugin\dh_bonuspunkte\source\interfaces\rewards;

interface IReward
{
    /**
     * Each reward could be initiated by a shop hook (event dispatcher), that
     * provides an array of arguments and additional data which can be used later.
     * @param array $arguments arguments of event dispatcher, otherwise empty array
     */
    public function __construct(array $arguments = []);
}