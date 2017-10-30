<?php declare(strict_types = 1);
namespace arhone\builder;

/**
 * Внедрение зависимостей
 *
 * Interface BuilderInterface
 * @package arhone\builder
 */
interface BuilderInterface {

    /**
     * Builder constructor.
     *
     * @param array $config
     */
    public function __construct (array $config = []);

    /**
     * Устанавливает значение для Alias
     *
     * @param string $alias
     * @param $value
     */
    public function set (string $alias, $value);

    /**
     * Возвращает результат сборки
     *
     * @param array $instruction
     * @return mixed
     */
    public static function make ($instruction);

    /**
     * Проверяет инъекцию на существование
     *
     * @param string $name
     * @return bool
     */
    public static function has (string $name) : bool;

    /**
     * Дополняет набор правил для удовлетворения зависимостей
     *
     * @param array $instruction
     * @return array
     */
    public static function instruction (array $instruction) : array;

    /**
     * Метод для установки настроек класса
     *
     * @param array $config
     * @return array
     */
    public static function config (array $config) : array;

}