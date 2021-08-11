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

function mySortForKey($a, $b)
{
    $idErr = -1;

    foreach ($a as $key => $item) {
        !array_key_exists($b, $item) ? $idErr = $key : false;
    }

    if ($idErr >= 0) {
        throw new Exception('В подмассиве ' . $idErr . ' отсутствует ключ "' . $b . '"');
    } else {
        uasort($a, function ($i, $j) use ($b, $a) {
            return $i[$b] > $j[$b] ? 1 : 0;
        });
    }

    return $a;
}


$arr =  [['a' => 2, 'b' => 33], ['b' => 34], ['a' => 77, 'b' => 55], ['a' => 54, 'b' => 65], ['a' => 4455, 'b' => 563], ['a' => 57, 'b' => 0], ['a' => 9, 'b' => 1055]];

echo "<pre> исходный массив \n";
print_r(mySortForKey($arr, 'b'));
echo "</pre>";

echo "<pre> отсортированный массив \n";
print_r(mySortForKey($arr, 'b'));
echo "</pre>";


?>