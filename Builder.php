<?php declare(strict_types = 1);

namespace arhone\builder;

/**
 * Внедрение зависимостей
 *
 * Class Builder
 * @package arhone\builder
 */
class Builder {

    /**
     * Конфигурация класса
     *
     * @var array
     */
    protected static $config = [
        'cache'    => true,
        'cacheDir' => __DIR__ . '/../../../cache/builder',
        'new'      => false,
        'clone'    => true,
        'defaultType' => 'alias'
    ];

    /**
     * Инструкции внедрения зависимостей
     *
     * @var array
     */
    protected static $instruction = [];

    /**
     * Хранилище инъекций
     *
     * @var array
     */
    protected static $storage = [];

    /**
     * Builder constructor.
     */
    public function __construct () {

        $this->set('DI', $this);

    }

    /**
     * Добавляет объект в хранилище
     *
     * @param string $name
     * @param $data
     */
    public static function set (string $name, $data) {

        self::$storage[$name] = $data;

    }

    /**
     * Получение объекта
     *
     * @param string $alias
     * @return mixed
     * @throws \Exception
     */
    public static function get (string $alias) {

        if (!isset(self::$storage[$alias]) && isset(self::$instruction[$alias])) {

            self::$storage[$alias] = self::make(self::$instruction[$alias]);

        }

        if (isset(self::$storage[$alias])) {

            $type = 'data';
            foreach (['class', 'callback', 'object', 'alias'] as $key => $value) {

                if (isset(self::$instruction[$alias][$value])) {

                    $type = $value; break;

                }

            }

            $method = 'get' . ucfirst($type);
            return self::$method($alias);

        } else {

            throw new \Exception('Builder: Отсутствует настройка для ' . $alias);

        }

    }

    /**
     * Возвращает класс
     *
     * @param $alias
     * @return object
     * @throws \Exception
     */
    protected static function getClass (string $alias) : object {

        return !empty(self::$instruction[$alias]['new'])
            ? self::make(self::$instruction[$alias])
            : (
            !empty(self::$instruction[$alias]['clone'])
                ? clone self::$storage[$alias]
                : self::$storage[$alias]
            );

    }

    /**
     * Возвращает значение превдонима
     *
     * @param $alias
     * @return mixed
     * @throws \Exception
     */
    protected static function getAlias (string $alias) {

        return self::get($alias);

    }

    /**
     * Возвращает результат вызова функции
     *
     * @param string $alias
     * @return mixed
     */
    protected static function getCallback (string $alias) {

        return self::$storage[$alias]->__invoke(...self::make(self::$instruction[$alias]['argument'] ?? []));

    }

    /**
     * Возвращает объект
     *
     * @param string $alias
     * @return object
     */
    protected static function getObject (string $alias) : object {

        return !empty(self::$instruction[$alias]['clone'])
            ? clone self::$storage[$alias]
            : self::$storage[$alias];

    }

    /**
     * Возвращает значение
     *
     * @param string $alias
     * @return mixed
     */
    protected static function getData (string $alias) {

        return self::$storage[$alias];

    }

    /**
     * Возвращает результат сборки настроек
     *
     * @param array $instruction
     * @return array
     * @throws \Exception
     */
    protected static function makeAll (array $instruction = []) : array {

        $arg = [];
        foreach ($instruction as $instruct) {
            $arg[] = self::make($instruct);
        }

        return $arg;

    }

    /**
     * Возвращает результат сборки
     *
     * @param array $instruction
     * @return mixed
     */
    public static function make (array $instruction) {

        $type = key($instruction);
        if (!$type) {
            return self::makeAlias([
                'alias' => current($instruction)
            ]);
        }

        $type = 'data';
        foreach (['class', 'object', 'alias', 'array', 'string', 'integer', 'float', 'bool'] as $key => $value) {

            if (isset($instruction[$value])) {

                $type = $value; break;

            }

        }

        $method = 'make' . ucfirst($type);
        return self::$method($instruction);

    }

    /**
     * Создаёт и возвращает готовый экземпляр класса
     *
     * @param array $instruction
     * @return mixed
     * @throws \Exception
     */
    protected static function makeClass (array $instruction) {

        if (isset($instruction['require'])) {

            if (file_exists($instruction['require'])) {

                $require = function ($require) {
                    require_once $require;
                };
                $require($instruction['require']);

            } else {

                throw new \Exception('DI: Отсутствует файл ' . $instruction['require']);

            }

        }

        $Object = new $instruction['class'](...self::makeAll($instruction['construct'] ?? []));

        if (isset($instruction['property'])) {

            foreach ($instruction['property'] as $property => $pInstruction) {

                $Object->{$property} = self::make($pInstruction);

            }

        }

        if (isset($instruction['method'])) {

            foreach ($instruction['method'] as $method => $mInstruction) {

                $Object->$method(...self::makeAll($mInstruction ?? []));

            }

        }

        return $Object;

    }
    
    /**
     * Возвращает объект
     *
     * @param array $instruction
     * @return object
     */
    protected static function makeObject (array $instruction) : object {
        
        return (object)$instruction['object'];

    }

    /**
     * Возвращает массив
     *
     * @param array $instruction
     * @return array
     */
    protected static function makeArray (array $instruction) : array {
        
        return (array)$instruction['array'];
        
    }

    /**
     * Возвращает строку
     *
     * @param array $instruction
     * @return string
     */
    protected static function makeString (array $instruction) : string {
        
        return (string)$instruction['string'];
        
    }

    /**
     * Возвращает число
     *
     * @param array $instruction
     * @return int
     */
    protected static function makeInteger (array $instruction) : int {
        
        return (integer)$instruction['integer'];
        
    }

    /**
     * Возвращает число с плавающей точкой
     *
     * @param array $instruction
     * @return float
     */
    protected static function makeFloat (array $instruction) : float {
        
        return (float)$instruction['float'];
        
    }

    /**
     * Возвращает значение псевдонима
     *
     * @param array $instruction
     * @return mixed
     */
    protected static function makeAlias (array $instruction) {
        
        return self::get($instruction['alias']);
        
    }

    /**
     * Возвращает значение
     *
     * @param array $instruction
     * @return mixed
     */
    protected static function makeData (array $instruction) {
        return current($instruction);
    }

    /**
     * Проверяет инъекцию на существование
     *
     * @param string $name
     * @return bool
     */
    public static function has (string $name) : bool {

        return isset(self::$instruction[$name]);

    }

    /**
     * Дополняет набор правил для удовлетворения зависимостей
     *
     * @param array $instruction
     * @return array
     */
    public static function instruction (array $instruction) : array {

        return self::$instruction = array_merge(self::$instruction, $instruction);

    }

    /**
     * Метод для установки настроек класса
     *
     * @param array $config
     * @return array
     */
    public static function config (array $config) : array {

        return self::$config = array_merge(self::$config, $config);

    }

}