<?php
namespace Millwright\Util;

class NumberToString
{
    protected static function getData()
    {
        return array(
            '1'          => array(
                1 => 'одна',
                2 => 'две',
            ),
            '9'          => array(
                1  => 'один',
                2  => 'два',
                3  => 'три',
                4  => 'четыре',
                5  => 'пять',
                6  => 'шесть',
                7  => 'семь',
                8  => 'восемь',
                9  => 'девять',
                10 => 'десять',
                11 => 'одиннацать',
                12 => 'двенадцать',
                13 => 'тринадцать',
                14 => 'четырнадцать',
                15 => 'пятнадцать',
                16 => 'шестнадцать',
                17 => 'семнадцать',
                18 => 'восемнадцать',
                19 => 'девятнадцать',
            ),
            '10'         => array(
                2 => 'двадцать',
                3 => 'тридцать',
                4 => 'сорок',
                5 => 'пятьдесят',
                6 => 'шестьдесят',
                7 => 'семьдесят',
                8 => 'восемдесят',
                9 => 'девяносто',
            ),
            '100'        => array(
                1 => 'сто',
                2 => 'двести',
                3 => 'триста',
                4 => 'четыреста',
                5 => 'пятьсот',
                6 => 'шестьсот',
                7 => 'семьсот',
                8 => 'восемьсот',
                9 => 'девятьсот',
            ),

            '1000'       => array(
                1 => 'тысяча',
                2 => 'тысячи',
                3 => 'тысяч',
            ),
            '1000000'    => array(
                1 => 'миллион',
                2 => 'миллиона',
                3 => 'миллионов',
            ),
            '1000000000' => array(
                1 => 'миллиард',
                2 => 'миллиарда',
                3 => 'миллиардов',
            ),

            'rub'        => array(
                1 => 'рубль',
                2 => 'рубля',
                3 => 'рублей',
            ),
            'kop'        => array(
                1 => 'копейка',
                2 => 'копейки',
                3 => 'копеек',
            ),
        );
    }

    protected static function addWord($value, $del, &$result, &$a)
    {
        $result[] = $a[$del][intval($value / $del)];
        $value %= $del;

        return $value;
    }

    protected static function getSemantic(&$value, $digits = 1, $suffix = '')
    {
        $result = array();
        $a      = static::getData();

        if ($value == 0 && $suffix) {
            if ($suffix == 'kop') {
                $result[] = '00';
            }
            $result[] = $a[$suffix][3];

            return $result;
        }

        if ($value < $digits) {
            return $result;
        }

        $i = intval($value / $digits);
        $value %= $digits;

        if ($i >= 100) {
            $i = static::addWord($i, 100, $result, $a);
        }

        if ($i >= 20) {
            $i = static::addWord($i, 10, $result, $a);
        }

        if ($i) {
            $result[] = $a[(($i < 3 && $digits <= 1000 && $suffix != 'rub') ? 1 : 9)][$i];
        }

        $plural_count = ($i == 1) ? 1 : (($i >= 2 && $i <= 4) ? 2 : 3);
        if (!$suffix) {
            $suffix = $digits;
        }
        $result[] = $a[$suffix][$plural_count];

        return $result;
    }

    public static function numberToString($value)
    {
        $result = array();

        $kop = round(($value - floor($value)) * 100);
        $rub = intval($value);
        foreach (array(1000000000, 1000000, 1000) as $digits) {
            $result = array_merge($result, static::getSemantic($rub, $digits));
        }

        $result = array_merge($result, static::getSemantic($rub, 1, 'rub'));

        $result = array_merge($result, static::getSemantic($kop, 1, 'kop'));

        return implode(' ', $result);
    }
}
