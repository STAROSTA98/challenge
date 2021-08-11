<?php

function convertString($a, $b)
{
    $pattern = '/' . $b . '/ui';

    preg_match_all($pattern, $a, $mathes, PREG_OFFSET_CAPTURE);

    $b = implode(array_reverse(mb_str_split($b)));

    return substr_replace($a, $b, $mathes[0][1][1], strlen($b));

}
echo "Мама мыла раму мама мама <br>";
echo convertString("Мама мыла раму мама она мама", "мама");



?>