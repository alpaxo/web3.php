<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3\Formatters;

use Web3\Validators\TagValidator;

class OptionalQuantityFormatter implements IFormatter
{
    public static function format(mixed $value): string
    {
        if (TagValidator::validate($value)) {
            return $value;
        }

        return QuantityFormatter::format($value);
    }
}