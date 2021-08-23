<?php

namespace Test3;

class newBase
{
    static private $count = 0;
    static private $arSetName = [];

    /**
     * @param string $name
     */
    function __construct(string $name = '0')     // должно string, а было Int
    {
        if (empty($name)) {
            while (array_search(self::$count, self::$arSetName) != false) {
                ++self::$count;
            }
            $name = self::$count;
        }
        $this->name = $name;
        self::$arSetName[] = $this->name;
    }

    protected $name;      // нужно protected иначе дочерний класс не имеет  к свойству

    /**
     * @return string
     */
    public function getName(): string
    {
        return '*' . $this->name . '*';
    }

    protected $value;

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        $size = strlen(serialize($this->value));
        return strlen($size) + $size;
    }

    public function __sleep()
    {
        return ['value'];
    }

    /**
     * @return string
     */
    public function getSave(): string
    {
        $value = serialize($this->value);   // было обращение к локальной переменной, а нужно к свойству класса
        return $this->name . ':' . strlen($value) . ':' . $value; // serialize преобразовывает в строку, а значит нужно strlen
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
    private $type = null;
    private $size = 0;
    private $property = null;

    /**
     * @param mixed $value
     */
    public function setValue($value)
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
        $this->type = typeGet($this->value); // typeGet потму что переименовано из gettype
    }

    private function setSize()
    {
        if (is_subclass_of($this->value, "Test3\\newView")) {    // необходимо экранировать \n
            $this->size = parent::getSize() + 1 + strlen($this->property);
        } elseif ($this->type == 'test') {
            $this->size = parent::getSize();
        } else {
            $this->size = strlen($this->value);
        }
    }

    /**
     * @return string
     */
    public function __sleep()
    {
        return ['property'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            throw new Exception('The object doesn\'t have name');
        }
        return '"' . $this->name . '": ';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return ' type ' . $this->type . ';';
    }

    /**
     * @return string
     */
    public function getSize(): string
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
    public function getSave(): string
    {
        if ($this->type == 'object') {   // исправлено c test на object поскольку $this->value становится строкой, а вызывается метод объекта
            $this->value = $this->value->getSave();
        }
        return parent::getSave() . serialize($this->property);
    }

    /**
     * @return newView
     */
    static public function load(string $value): newView
    {
        $arValue = explode(':', $value);

        $obj = new newView($arValue[0]);    // тип объекта сменён с newBase на newView,
        // поскольку в newBase отсутствует метод setProperty
        // а также сначала создан объект
        $obj->setValue(unserialize(substr($value, strlen($arValue[0]) + 1   // и вызваны его методы
            + strlen($arValue[1]) + 1)));  // убрано $arValue[1]
        $obj->setProperty(unserialize(substr($value, strlen($arValue[0]) + 1
            + strlen($arValue[1]) + 1 + $arValue[1])));
        return ($obj);  // а затем возвращается данный объект

    }
}

function typeGet($value): string   // функция переименована потому что совпадает с служебной функцией gettype
{
    if (is_object($value)) {
        $type = get_class($value);
        do {
            if (strpos($type, "Test3\\newBase") !== false) {    // необходимо экранировать \n
                return 'test';
            }
        } while ($type = get_parent_class($type));
    }
    return gettype($value);
}


$obj = new newBase('12345');
$obj->setValue('text');

$obj2 = new \Test3\newView('O9876');
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

$obj2->getSave();

var_dump($obj2->getSave() == $obj3->getSave());

