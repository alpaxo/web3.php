<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3;

use InvalidArgumentException;
use stdClass;
use kornrunner\Keccak;
use phpseclib3\Math\BigInteger as BigNumber;
use ValueError;

class Utils
{
    /**
     * SHA3_NULL_HASH
     */
    const SHA3_NULL_HASH = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';

    /**
     * UNITS
     * from ethjs-unit
     * 
     * @const array
     */
    const UNITS = [
        'noether' => '0',
        'wei' => '1',
        'kwei' => '1000',
        'Kwei' => '1000',
        'babbage' => '1000',
        'femtoether' => '1000',
        'mwei' => '1000000',
        'Mwei' => '1000000',
        'lovelace' => '1000000',
        'picoether' => '1000000',
        'gwei' => '1000000000',
        'Gwei' => '1000000000',
        'shannon' => '1000000000',
        'nanoether' => '1000000000',
        'nano' => '1000000000',
        'szabo' => '1000000000000',
        'microether' => '1000000000000',
        'micro' => '1000000000000',
        'finney' => '1000000000000000',
        'milliether' => '1000000000000000',
        'milli' => '1000000000000000',
        'ether' => '1000000000000000000',
        'kether' => '1000000000000000000000',
        'grand' => '1000000000000000000000',
        'mether' => '1000000000000000000000000',
        'gether' => '1000000000000000000000000000',
        'tether' => '1000000000000000000000000000000'
    ];

    /**
     * NEGATIVE1
     * Cannot work, see: http://php.net/manual/en/language.constants.syntax.php
     * 
     * @const
     */
    // const NEGATIVE1 = new BigNumber(-1);

    /**
     * construct
     *
     * @return void
     */
    // public function __construct() {}

    /**
     * Encoding string or integer or numeric string(is not zero prefixed) or big number to hex.
     */
    public static function toHex(string|int|float|BigNumber $value, bool $isPrefix = false): string
    {
        if (is_numeric($value)) {
            // turn to hex number
            $bn = self::toBn($value);
            $hex = $bn->toHex(true);
            $hex = preg_replace('/^0+(?!$)/', '', $hex);
        } elseif (is_string($value)) {
            $value = self::stripZero($value);
            $hex = implode('', unpack('H*', $value));
        } elseif ($value instanceof BigNumber) {
            $hex = $value->toHex(true);
            $hex = preg_replace('/^0+(?!$)/', '', $hex);
        } else {
            throw new InvalidArgumentException('The value to toHex function is not support.');
        }
        if ($isPrefix) {
            return '0x' . $hex;
        }
        return $hex;
    }

    public static function hexToBin(string $value): string
    {
        if (self::isZeroPrefixed($value)) {
            $value = str_replace('0x', '', $value);
        }

        return pack('H*', $value);
    }

    public static function isZeroPrefixed(string $value):bool
    {
        return str_starts_with($value, '0x');
    }

    public static function stripZero(string $value): string
    {
        if (self::isZeroPrefixed($value)) {
            return str_replace('0x', '', $value);
        }

        return $value;
    }

    public static function isNegative(string $value): bool
    {
        return str_starts_with($value, '-');
    }

    public static function isAddress(string $value): bool
    {
        if (preg_match('/^(0x|0X)?[a-f0-9A-F]{40}$/', $value) !== 1) {
            return false;
        } elseif (preg_match('/^(0x|0X)?[a-f0-9]{40}$/', $value) === 1 || preg_match('/^(0x|0X)?[A-F0-9]{40}$/', $value) === 1) {
            return true;
        }

        return self::isAddressChecksum($value);
    }

    public static function isAddressChecksum(string $value): bool
    {
        $value = self::stripZero($value);
        $hash = self::stripZero(self::sha3(mb_strtolower($value)));

        for ($i = 0; $i < 40; $i++) {
            if (
                (intval($hash[$i], 16) > 7 && mb_strtoupper($value[$i]) !== $value[$i]) ||
                (intval($hash[$i], 16) <= 7 && mb_strtolower($value[$i]) !== $value[$i])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public static function toChecksumAddress(string $value): string
    {
        $value = self::stripZero(strtolower($value));
        $hash = self::stripZero(self::sha3($value));
        $ret = '0x';

        for ($i = 0; $i < 40; $i++) {
            if (intval($hash[$i], 16) >= 8) {
                $ret .= strtoupper($value[$i]);
            } else {
                $ret .= $value[$i];
            }
        }

        return $ret;
    }

    public static function isHex(mixed $value): bool
    {
        return (is_string($value) && preg_match('/^(0x)?[a-f0-9]*$/', $value) === 1);
    }

    /**
     * @throws \Exception
     */
    public static function sha3(string $value): ?string
    {
        if (str_starts_with($value, '0x')) {
            $value = self::hexToBin($value);
        }

        $hash = Keccak::hash($value, 256);

        if ($hash === self::SHA3_NULL_HASH) {
            return null;
        }

        return '0x' . $hash;
    }

    public static function toString(mixed $value): string
    {
        return (string) $value;
    }

    /**
     * Change number from unit to wei.
     * For example:
     * $wei = Utils::toWei('1', 'kwei'); 
     * $wei->toString(); // 1000
     */
    public static function toWei(BigNumber|string $number, string $unit): BigNumber
    {
        $bn = self::toBn($number);

        if (!isset(self::UNITS[$unit])) {
            throw new InvalidArgumentException('toWei doesn\'t support ' . $unit . ' unit.');
        }

        $bnt = new BigNumber(self::UNITS[$unit]);

        if (is_array($bn)) {
            // fraction number
            list($whole, $fraction, $fractionLength, $negative1) = $bn;

            if ($fractionLength > strlen(self::UNITS[$unit])) {
                throw new InvalidArgumentException('toWei fraction part is out of limit.');
            }

            $whole = $whole->multiply($bnt);

            // There is no pow function in phpseclib 2.0, only can see in dev-master
            // Maybe implement own biginteger in the future
            // See 2.0 BigInteger: https://github.com/phpseclib/phpseclib/blob/2.0/phpseclib/Math/BigInteger.php
            // See dev-master BigInteger: https://github.com/phpseclib/phpseclib/blob/master/phpseclib/Math/BigInteger.php#L700
            // $base = (new BigNumber(10))->pow(new BigNumber($fractionLength));

            // So we switch phpseclib special global param, change in the future
            $powerBase = match (BigNumber::getEngine()[0]) {
                'GMP' => gmp_pow(gmp_init(10), (int)$fractionLength),
                'BCMath' => bcpow('10', (string)$fractionLength, 0),
                default => pow(10, (int)$fractionLength),
            };

            $base = new BigNumber($powerBase);

            $fraction = $fraction->multiply($bnt)->divide($base)[0];

            if ($negative1 !== false) {
                return $whole->add($fraction)->multiply($negative1);
            }

            return $whole->add($fraction);
        }

        return $bn->multiply($bnt);
    }

    /**
     * Change number from unit to ether.
     * For example:
     * list($bnq, $bnr) = Utils::toEther('1', 'kether'); 
     * $bnq->toString(); // 1000
     */
    public static function toEther(BigNumber|string|int $number, string $unit): array
    {
        // if ($unit === 'ether') {
        //     throw new InvalidArgumentException('Please use another unit.');
        // }
        $wei = self::toWei($number, $unit);

        $bnt = new BigNumber(self::UNITS['ether']);

        return $wei->divide($bnt);
    }

    /**
     * Change number from wei to unit.
     * For example:
     * list($bnq, $bnr) = Utils::fromWei('1000', 'kwei'); 
     * $bnq->toString(); // 1
     */
    public static function fromWei(BigNumber|string|int $number, string $unit): BigNumber|array
    {
        $bn = self::toBn($number);

        if (!isset(self::UNITS[$unit])) {
            throw new InvalidArgumentException('fromWei doesn\'t support ' . $unit . ' unit.');
        }

        $bnt = new BigNumber(self::UNITS[$unit]);

        return $bn->divide($bnt);
    }

    public static function jsonMethodToString(stdClass|array $json): string
    {
        if ($json instanceof stdClass) {
            // one way to change whole json stdClass to array type
            // $jsonString = json_encode($json);

            // if (JSON_ERROR_NONE !== json_last_error()) {
            //     throw new InvalidArgumentException('json_decode error: ' . json_last_error_msg());
            // }
            // $json = json_decode($jsonString, true);

            // another way to change whole json to array type but need the depth
            // $json = self::jsonToArray($json, $depth)

            // another way to change json to array type but not whole json stdClass
            $json = (array) $json;
            $typeName = [];

            foreach ($json['inputs'] as $param) {
                if (isset($param->type)) {
                    $typeName[] = $param->type;
                }
            }

            return $json['name'] . '(' . implode(',', $typeName) . ')';
        }

        if (isset($json['name']) && strpos($json['name'], '(') > 0) {
            return $json['name'];
        }

        $typeName = [];

        foreach ($json['inputs'] as $param) {
            if (isset($param['type'])) {
                $typeName[] = $param['type'];
            }
        }

        return $json['name'] . '(' . implode(',', $typeName) . ')';
    }

    public static function jsonToArray(stdClass|array|string $json): array|string
    {
        if ($json instanceof stdClass) {
            $json = (array) $json;
            $typeName = [];

            foreach ($json as $key => $param) {
                if (is_array($param)) {
                    foreach ($param as $subKey => $subParam) {
                        $json[$key][$subKey] = self::jsonToArray($subParam);
                    }
                } elseif ($param instanceof stdClass) {
                    $json[$key] = self::jsonToArray($param);
                }
            }
        } elseif (is_array($json)) {
            foreach ($json as $key => $param) {
                if (is_array($param)) {
                    foreach ($param as $subKey => $subParam) {
                        $json[$key][$subKey] = self::jsonToArray($subParam);
                    }
                } elseif ($param instanceof stdClass) {
                    $json[$key] = self::jsonToArray($param);
                }
            }
        }

        return $json;
    }

    /**
     * Change number or number string to bignumber.
     */
    public static function toBn(BigNumber|string|int|float $number): array|BigNumber
    {
        if ($number instanceof BigNumber){
            $bn = $number;
        } elseif (is_int($number)) {
            $bn = new BigNumber($number);
        } elseif (is_numeric($number)) {
            $number = (string) $number;

            if (self::isNegative($number)) {
                $count = 1;
                $number = str_replace('-', '', $number, $count);
                $negative1 = new BigNumber(-1);
            }

            if (strpos($number, '.') > 0) {
                $comps = explode('.', $number);

                if (count($comps) > 2) {
                    throw new InvalidArgumentException('toBn number must be a valid number.');
                }
                $whole = $comps[0];
                $fraction = $comps[1];

                return [
                    new BigNumber($whole),
                    new BigNumber($fraction),
                    strlen($comps[1]),
                    $negative1 ?? false
                ];
            } else {
                $bn = new BigNumber($number);
            }

            if (isset($negative1)) {
                $bn = $bn->multiply($negative1);
            }
        } elseif (is_string($number)) {
            $number = mb_strtolower($number);

            if (self::isNegative($number)) {
                $count = 1;
                $number = str_replace('-', '', $number, $count);
                $negative1 = new BigNumber(-1);
            }

            if (self::isZeroPrefixed($number) || preg_match('/[a-f]+/', $number) === 1) {
                $number = self::stripZero($number);

                try {
                    $bn = new BigNumber($number, 16);
                }catch(ValueError) {
                    $bn = new BigNumber(0, 16);
                }

            } elseif (empty($number)) {
                $bn = new BigNumber(0);
            } else {
                throw new InvalidArgumentException('toBn number must be valid hex string.');
            }

            if (isset($negative1)) {
                $bn = $bn->multiply($negative1);
            }
        }

        return $bn;
    }
}