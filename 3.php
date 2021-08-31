<?php
namespace Test3;

class newBase
{
    static private $count = 1;		// начальный счётчик в единицу, покольку за 0 программа воспринимает как empty и выдаётся исключение
    static private $arSetName = [];
    /**
     * @param string $name
     */
    function __construct(string $name = '0')    // $name Должна быть string
    {
			
        if (empty($name)) {
            if (array_search(self::$count, self::$arSetName)) { // array_search сам по себе проходит по массиву, цикл не нужен, поставлено if, убрано != false, эквивалент true
				++self::$count;												
            }
            $name = self::$count;
        }
        $this->name = $name;
        self::$arSetName[] = $this->name;
    }
    protected $name;  // вместо private должно быть protected иначе дочерний класс не имеет доступа к свойству, а оно далее используется
    /**
     * @return string
     */
    public function getName(): string
    {
        return '*' . $this->name  . '*';
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
        $this->type = typeGet($this->value);
    }
    private function setSize()
    {
        if (is_subclass_of($this->value, "Test3\\newView")) {    // необходимо экранировать  \n иначе воспринимается как перенос строки
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
        return '"' . $this->name  . '": ';
    }
    /**
     * @return string
     */
    public function getType(): string
    {
        return ' type ' . $this->type  . ';';
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
        if ($this->type == 'object') {      // если тип равен объекту то только в этом случае
                                            // можно вызвать метод getSave, поскольку у строки невозможно вызвать метод
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

$obj2 = new \Test3\newView('O9876');
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

var_dump($obj2->getSave() == $obj3->getSave());


