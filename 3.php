<?php

declare(strict_types=1);

namespace Test3;

class newBase
{
    static private int $count = 0; // указан тип свойства
    static private array $arSetName = [0];  // нужен непустой массив иначе array_search не запустится изначально, указан тип свойства

    /**
     * @param string $name
     */
    function __construct(string $name = '0')    // $name Должна быть string, указан тип аргумента
    {
        if (empty($name)) {
            while (array_search(self::$count, self::$arSetName) !== false) { // добавлено тождественное сравнение, чтобы 0 не приравнивался к false
                ++self::$count;
            }
            $name = self::$count;
        }
        $this->name = $name;
        self::$arSetName[] = $this->name;
    }

    protected string $name;  // вместо private должно быть protected иначе дочерний класс не имеет доступа к свойству, а оно далее используется, указан тип свойства

    /**
     * @return string
     */
    public function getName(): string   // указан тип возвращаемого значения
    {
        return '*' . $this->name . '*';
    }

    protected mixed $value;     // указан тип свойства

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value)  // указан тип возвращаемого значения
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getSize(): string       // указан тип возвращаемого значения
    {
        $size = strlen(serialize($this->value));
        return (string)$size;       // необходимо вернуть string тип, убран strlen, т.к. длина высчитывается выше строкой
    }

    public function __sleep()
    {
        return ['value'];
    }

    /**
     * @return string
     */
    public function getSave(): string   // указан тип возвращаемого значения
    {
        $value = serialize($this->value);  // выполняется serialize лоакльной (пустой) переменной, должно использоваться свойство класса $value
        return $this->name . ':' . strlen($value) . ':' . $value;   // $value строка, а значит длина строки вычисляется через strlen
    }

    /**
     * @return newBase
     */
    static public function load(string $value): newBase
    {
        $arValue = explode(':', $value);
        return (new newBase($arValue[0]))
            ->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1), $arValue[1]));
    }
}

class newView extends newBase
{
    private ?string $type = null;      // указан тип свойства
    private ?string $size = null;     // изменено с int на string, указывает на размер, но во всех операциях используется как строка, указан тип свойства
    private ?string $property = null;   // указан тип свойства

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value)  // указан тип аргумента
    {
        parent::setValue($value);
        $this->setType();
        $this->setSize();
    }

    public function setProperty($value)
    {
        $this->property = $value;
        return $this;
    }

    private function setType()
    {
        $this->type = typeGet($this->value);
    }

    private function setSize()
    {
        if (is_subclass_of($this->value, "Test3\\newView")) {    // необходимо экранировать  \n иначе воспринимается как перенос строки
            $this->size = parent::getSize() + 1 + strlen($this->property);
        } elseif ($this->type == 'test') {
            $this->size = parent::getSize();
        } else {
            $this->size = (string)strlen($this->value); // добавлено приведение к строке
        }
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['property'];
    }

    /**
     * @return string
     */
    public function getName(): string    // указан тип возвращаемого значения
    {
        if (empty($this->name)) {
            throw new Exception('The object doesn\'t have name');
        }
        return '"' . $this->name . '": ';
    }

    /**
     * @return string
     */
    public function getType(): string    // указан тип возвращаемого значения
    {
        return ' type ' . $this->type . ';';
    }

    /**
     * @return string
     */
    public function getSize(): string    // указан тип возвращаемого значения
    {
        return ' size ' . $this->size . ';';
    }

    public function getInfo()
    {
        try {
            echo $this->getName()
                . $this->getType()
                . $this->getSize()
                . "\r\n";
        } catch (Exception $exc) {
            echo 'Error: ' . $exc->getMessage();
        }
    }

    /**
     * @return string
     */
    public function getSave(): string    // указан тип возвращаемого значения
    {
        if ($this->type == 'object') {      // если тип равен объекту то только в этом случае
            // можно вызвать метод getSave, поскольку у строки невозможно вызвать метод
            $this->value = $this->value->getSave();
        }
        return parent::getSave() . serialize($this->property);
    }

    /**
     * @return newView
     */
    static public function load(string $value): newView  // указан тип возвращаемого значения
    {

        $arValue = explode(':', $value);

        $newObj = new newView($arValue[0]);     // сначала создан экземплар класса
        // в классе newBase не существует метода setProperty,
        // соответственно нужно создавать экземпляр newView
        $newObj->setValue(unserialize(substr($value, strlen($arValue[0]) + 1    // выполнены его методы
            + strlen($arValue[1]) + 1), $arValue)); // unserialize в options должен принимать массив параметров
        $newObj->setProperty(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1 + $arValue[1])));
        return ($newObj);   // а затем возвращён объект
    }
}

function typeGet($value): string      // переименовано на typeGet поскольку gettype это служебная функция
{
    if (is_object($value)) {
        $type = get_class($value);
        do {
            if (strpos($type, "Test3\\newBase") !== false) {   // необходимо экранировать  \n иначе воспринимается как перенос строки
                return 'test';
            }
        } while ($type = get_parent_class($type));
    }
    return gettype($value);
}


$obj = new newBase('12345');
$obj->setValue('text');

$obj2 = new newView('O9876');
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

var_dump($obj2->getSave() == $obj3->getSave());


