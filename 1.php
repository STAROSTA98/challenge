<?php

    // массив простых чисел
    function findSimple(int $a, int $b): array
    {

        try {
           if(($a < 0) or ($b < 0)){
                throw new InvalidArgumentException('Элементы не могут быть отрицательными');
           } else if($a > $b) {
               throw new InvalidArgumentException('Начало массива не может быть больше конца');
           }

            $arr = [];
            foreach (range($a, $b) as $item){
                $arr[] = $item;
            }

            $simpleArr = [];

            foreach ($arr as $item) {
                if ($item % 2 == 0) {
                    continue;
                }

                $simple = true;

                for ($i = 3; $i < $item; $i++) {
                    if ($item % $i == 0) {
                        $simple = false;
                    }
                }

                if ($simple) {
                    $simpleArr[] = $item;
                }

            }
            return $simpleArr;

        } catch (InvalidArgumentException $e){

            echo $e->getMessage();

        }

    }

    // вывод массива
    var_dump(findSimple(50, 560));

    // заполнение трапеций
    function createTrapeze(array $a): array{

        $trapArr = array_chunk($a, 3);
        $tripleArr = ['a', 'b', 'c'];

        foreach ($trapArr as $key => $item){
            $trapArr[$key] = array_combine($tripleArr, $item);
        }

        return $trapArr;
    }

    $trapeze = createTrapeze([1, 2, 3, 4, 5, 6, 14, 55, 23, 36, 45, 66, 51, 208, 222, 112, 75, 83, 85, 179, 209, 109, 201, 39]);

    // площадь трапеций
    function squareTrapeze(&$a){

        foreach ($a as $key => $item) {
            $s = ($item['a'] + $item['b']) / 2 * $item['c'];
            $sArr = ['s' => $s];
            $a[$key] = array_merge($item, $sArr);
        }

    }

    // рассчёт площади
    squareTrapeze($trapeze);

    // максимальная площадь
    function getSizeForLimit(array $a, float $b): array{

        return array_filter($a, fn($e) => $e['s'] <= $b);

    }

    //вывод по пределу площади
    var_dump(getSizeForLimit($trapeze, 5000));

    // минимальное число массива
    function getMin($a){
        $min = $a[0];

        for ($i = 1; $i < count($a); $i++){
            $a[$i] < $min ? $min = $a[$i] : false;
        }

        return $min;
    }

    // вывод таблицы
    function printTrapeze($a){
        echo "<table border='1'>
                <tr>
                    <th>a</th>
                    <th>b</th>
                    <th>h</th>
                    <th>s</th>
                </tr>";
        foreach($a as $item){
            $s = $item['s'] - floor($item['s']);
            if(($s == 0) and ($item['s'] % 2 != 0)){
                echo "<tr bgcolor='red'>";
            } else{
                echo "<tr>";
            }

            foreach ($item as $value) {
                echo "<td>$value</td>";
            }

            echo "</tr>";
        }

        echo " </table>";
    }

    printTrapeze($trapeze);

    // абстрактный класс
    abstract class BaseMath{
        public function exp1($a, $b, $c){
            return $a * (pow($b, $c));
        }

        public function exp2($a, $b, $c){
            return pow($a / $b, $c);
        }

        abstract function getValue();
    }

    // класс потомок
    class F1 extends BaseMath{
        private $a, $b, $c;

        public function __construct($aConstr, $bConstr, $cConstr){
            $this->a = $aConstr;
            $this->b = $bConstr;
            $this->c = $cConstr;
        }


        function getValue(){
            return $this->exp1($this->a, $this->b, $this->c) + pow(pow($this->a / $this->b, $this->c) % 3, min($this->a, $this->b, $this->c));
        }
    }

    // создание экземпляра класса F1 и его реализация
    $ss = new F1(1, 2, 3);

    echo $ss->getValue();
?>