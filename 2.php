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

function conMySQL()
{
    $host = 'localhost';
    $db = 'test_samson';
    $user = 'root';
    $pass = '';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    return new PDO($dsn, $user, $pass, $opt);
}

function importXml($a)
{
    $pdo = conMySQL();


    function setIns($val, $fields = [])
    {

        if (!empty($fields)) {
            $extSql = '(';
            foreach ($fields as $field) {
                $extSql .= $field . ',';
            }
            $extSql = substr_replace($extSql, ') VALUES (', -1);
            foreach ($val as $item) {
                is_string($item) ? $extSql .= "'" . $item . "'," : $extSql .= $item . ",";
            }
            $extSql = substr_replace($extSql, ');', -1);
        } else {
            $extSql = 'VALUES (';
            foreach ($val as $item) {
                is_string($item) ? $extSql .= "'" . $item . "'," : $extSql .= $item . ",";
            }
            $extSql = substr_replace($extSql, ');', -1);
        }

        return $extSql;
    }

    if (file_exists($a)) {
        $xml = simplexml_load_file($a);
    }

    foreach ($xml as $tovar) {

        $arrVal = [$tovar['Код'], (string)$tovar['Название']];
        $arrFields = ['kod', 'name'];
        $sql = 'INSERT INTO a_product ' . setIns($arrVal, $arrFields);
        $stm = $pdo->prepare($sql);
        $stm->execute();

        $idTovar = $pdo->lastInsertId();

        $arrVal = [];
        $arrFields = ['tovar', 'propety'];

        foreach ($tovar->Свойства as $property) {

            $arrVal = [$idTovar, json_encode($property, JSON_UNESCAPED_UNICODE)];

        }

        $sql = 'INSERT INTO a_property ' . setIns($arrVal, $arrFields);
        $stm = $pdo->prepare($sql);
        $stm->execute();

        $arrFields = ['tovar', 'type_price', 'price'];

        foreach ($tovar->Цена as $key => $item) {
            $arrVal = [$idTovar, (string)$item['Тип'], (float)$item];
            $sql = 'INSERT INTO a_price ' . setIns($arrVal, $arrFields);
            $stm = $pdo->prepare($sql);
            $stm->execute();
        }

        foreach ($tovar->Разделы as $category) {
            foreach ($category as $item) {

                $arrFields = ['kod', 'name', 'parent_kod'];

                if ($item['Родитель']) {
                    $arrVal = [$item['Код'], (string)$item, $item['Родитель']];
                } else {
                    $arrVal = [$item['Код'], (string)$item, 'NULL'];
                }
                $sql = 'INSERT IGNORE INTO a_category ' . setIns($arrVal, $arrFields);
                $stm = $pdo->prepare($sql);
                $stm->execute();

                $arrVal = [$tovar['Код'], $item['Код']];
                $arrFields = ['tovar', 'category'];
                $sql = 'INSERT INTO product_category ' . setIns($arrVal, $arrFields);
                $stm = $pdo->prepare($sql);
                $stm->execute();

            }

        }

    }

}

importXml('tovar.xml');

function exportXml($a, $b)
{
    $pdo = conMySQL();
    $iXML = new DOMDocument('1.0', 'utf-8');

    $root = $iXML->createElement("Товары");
    $iXML->appendChild($root);

    $stmt = $pdo->prepare("SELECT tovar FROM product_category WHERE category = ?;");
    $stmt->execute(array($b));
    while ($row = $stmt->fetch(PDO::FETCH_LAZY)) {


        $stmtTovar = $pdo->prepare("SELECT * FROM a_product WHERE kod = ?");
        $stmtTovar->execute(array($row[0]));
        $rowTovar = $stmtTovar->fetch(PDO::FETCH_LAZY);
        $id = $rowTovar['id'];
        $kod = $rowTovar['kod'];
        $name = $rowTovar['name'];

        $tovar = $iXML->createElement('Товар');
        $tovar->setAttribute('Код', $kod);
        $tovar->setAttribute('Название', $name);

        $stmtPrice = $pdo->prepare("SELECT * FROM a_price WHERE tovar = ?");
        $stmtPrice->execute(array($id));

        while ($rowPrice = $stmtPrice->fetch(PDO::FETCH_LAZY)) {
            $price = $iXML->createElement('Цена', $rowPrice['price']);
            $price->setAttribute('Тип', $rowPrice['type_price']);
            $tovar->appendChild($price);
        }

        $genProperty = $iXML->createElement('Свойства');

        $stmtProperty = $pdo->prepare("SELECT * FROM a_property WHERE tovar = ?");
        $stmtProperty->execute(array($id));

        $arrProperty = json_decode($stmtProperty->fetchColumn(1));
        foreach ($arrProperty as $key => $prop) {
            if (is_array($prop)) {
                foreach ($prop as $item) {
                    $property = $iXML->createElement($key, $item);
                    $genProperty->appendChild($property);
                }
            } else {
                $property = $iXML->createElement($key, $prop);
                $genProperty->appendChild($property);
            }
        }

        $tovar->appendChild($genProperty);

        $genCategory = $iXML->createElement('Разделы');

        $stmtProperty = $pdo->prepare("SELECT a_category.name, a_category.parent_kod, product_category.tovar, product_category.category
                                    FROM a_category INNER JOIN product_category ON a_category.kod = product_category.category
                                    WHERE product_category.tovar = ?
                                    GROUP BY product_category.category");
        $stmtProperty->execute(array($kod));

        while ($rowCategory = $stmtProperty->fetch(PDO::FETCH_LAZY)) {
            $category = $iXML->createElement('Раздел', $rowCategory['name']);
            $category->setAttribute('Код', $rowCategory['category']);
            if ($rowCategory['parent_kod']) {
                $category->setAttribute('Родитель', $rowCategory['parent_kod']);
            }
            $genCategory->appendChild($category);

        }

        $tovar->appendChild($genCategory);

        $root->appendChild($tovar);

        $iXML->save($a);
    }
}

exportXml("import_tovar.xml", 3);

?>