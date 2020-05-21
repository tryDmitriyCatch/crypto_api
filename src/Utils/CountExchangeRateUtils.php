<?php

namespace App\Utils;

/**
 * Class CountExchangeRateUtils
 */
class CountExchangeRateUtils
{
    /**
     * @param $exchangeArray
     * @param $assetValue
     * @return float|int
     */
    public static function getTotalExchangeSum($exchangeArray, $assetValue)
    {
        return $assetValue * $exchangeArray['rate'];
    }
}
