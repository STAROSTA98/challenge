<?php

function convertString(string $a, string $b): string
{
    $pattern = '/' . $b . '/ui';

    if (preg_match_all($pattern, $a, $mathes, PREG_OFFSET_CAPTURE)) {
        $b = implode(array_reverse(mb_str_split($b)));
        return substr_replace($a, $b, $mathes[0][1][1], strlen($b));
    } else {
        return "Строка $b не найдена";
    }

}

echo "Мама мыла раму мама она мама <br>";
echo convertString("Мама мыла раму мама она мама", "мама");

function mySortForKey(array $a, string $b): array
{

    foreach ($a as $key => $item) {
        if (!array_key_exists($b, $item)) {
            throw new Exception('В подмассиве ' . $key . ' отсутствует ключ "' . $b . '"');
        }
    }

    uasort($a, function ($i, $j) use ($b) {
        return $i[$b] > $j[$b] ? 1 : 0;
    });

    return $a;
}

$arr = [['a' => 2, 'b' => 33], ['b' => 34], ['a' => 77, 'b' => 55], ['a' => 54, 'b' => 65], ['a' => 4455, 'b' => 563], ['a' => 57, 'b' => 0], ['a' => 9, 'b' => 1055]];

echo "<pre> исходный массив \n";
print_r(mySortForKey($arr, 'b'));
echo "</pre>";

echo "<pre> отсортированный массив \n";
print_r(mySortForKey($arr, 'b'));
echo "</pre>";

function conMySQL()     // подключение к БД
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

function importXml(string $a): void    // импорт в БД из файла
{
    $pdo = conMySQL();

    function setIns($val, $fields = []) // создание SQL Запроса на добавление данных
    {

        if (!empty($fields)) {      //если список полей не пустой то работать вставлять по полям
            $extSql = '(';
            foreach ($fields as $field) {
                $extSql .= $field . ',';
            }
            $extSql = substr_replace($extSql, ') VALUES (', -1);
            foreach ($val as $item) {
                is_string($item) ? $extSql .= "'" . $item . "'," : $extSql .= $item . ",";
            }
            $extSql = substr_replace($extSql, ');', -1);
        } else {            // если список полей пустой то вставлять все поля подряд
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

        foreach ($xml as $product) {

            $arrVal = [$product['Код'], (string)$product['Название']];
            $arrFields = ['code', 'name'];
            $sql = 'INSERT INTO a_product ' . setIns($arrVal, $arrFields);
            $stm = $pdo->prepare($sql);
            $stm->execute();

            $idproduct = $pdo->lastInsertId();

            $arrVal = [];
            $arrFields = ['product', 'propety'];

            foreach ($product->Свойства as $property) {
                $arrVal = [$idproduct, json_encode($property, JSON_UNESCAPED_UNICODE)];
            }

            $sql = 'INSERT INTO a_property ' . setIns($arrVal, $arrFields);
            $stm = $pdo->prepare($sql);
            $stm->execute();

            $arrFields = ['product', 'type_price', 'price'];

            foreach ($product->Цена as $key => $item) {
                $arrVal = [$idproduct, (string)$item['Тип'], (float)$item];
                $sql = 'INSERT INTO a_price ' . setIns($arrVal, $arrFields);
                $stm = $pdo->prepare($sql);
                $stm->execute();
            }

            foreach ($product->Разделы as $category) {
                foreach ($category as $item) {

                    $arrFields = ['code', 'name', 'parent_code'];

                    if ($item['Родитель']) {
                        $arrVal = [$item['Код'], (string)$item, $item['Родитель']];
                    } else {
                        $arrVal = [$item['Код'], (string)$item, 'NULL'];
                    }
                    $sql = 'INSERT IGNORE INTO a_category ' . setIns($arrVal, $arrFields);
                    $stm = $pdo->prepare($sql);
                    $stm->execute();

                    $arrVal = [$product['Код'], $item['Код']];
                    $arrFields = ['product', 'category'];
                    $sql = 'INSERT INTO product_category ' . setIns($arrVal, $arrFields);
                    $stm = $pdo->prepare($sql);
                    $stm->execute();

                }

            }

        }

    }

}

importXml('product.xml');

function exportXml($a, $b): void  // экспорт данных из БД в файл по заданной рубрике
{
    $pdo = conMySQL();
    $iXML = new DOMDocument('1.0', 'utf-8');

    $root = $iXML->createElement("Товары");
    $iXML->appendChild($root);

    $stmt = $pdo->prepare("SELECT product FROM product_category WHERE category = ?;");
    $stmt->execute(array($b));
    while ($row = $stmt->fetch(PDO::FETCH_LAZY)) {


        $stmtproduct = $pdo->prepare("SELECT * FROM a_product WHERE code = ?");
        $stmtproduct->execute(array($row[0]));
        $rowproduct = $stmtproduct->fetch(PDO::FETCH_LAZY);
        $id = $rowproduct['id'];
        $code = $rowproduct['code'];
        $name = $rowproduct['name'];

        $product = $iXML->createElement('Товар');
        $product->setAttribute('Код', $code);
        $product->setAttribute('Название', $name);

        $stmtPrice = $pdo->prepare("SELECT * FROM a_price WHERE product = ?");
        $stmtPrice->execute(array($id));

        while ($rowPrice = $stmtPrice->fetch(PDO::FETCH_LAZY)) {
            $price = $iXML->createElement('Цена', $rowPrice['price']);
            $price->setAttribute('Тип', $rowPrice['type_price']);
            $product->appendChild($price);
        }

        $genProperty = $iXML->createElement('Свойства');

        $stmtProperty = $pdo->prepare("SELECT * FROM a_property WHERE product = ?");
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

        $product->appendChild($genProperty);

        $genCategory = $iXML->createElement('Разделы');

        $stmtProperty = $pdo->prepare("SELECT a_category.name, a_category.parent_code, product_category.product, product_category.category
                                    FROM a_category INNER JOIN product_category ON a_category.code = product_category.category
                                    WHERE product_category.product = ?
                                    GROUP BY product_category.category");
        $stmtProperty->execute(array($code));

        while ($rowCategory = $stmtProperty->fetch(PDO::FETCH_LAZY)) {
            $category = $iXML->createElement('Раздел', $rowCategory['name']);
            $category->setAttribute('Код', $rowCategory['category']);
            if ($rowCategory['parent_code']) {
                $category->setAttribute('Родитель', $rowCategory['parent_code']);
            }
            $genCategory->appendChild($category);

        }

        $product->appendChild($genCategory);

        $root->appendChild($product);

        $iXML->save($a);
    }
}

exportXml("import_product.xml", 3);

?>