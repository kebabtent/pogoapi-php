<?php
namespace POGOAPI\Util;

use ReflectionClass;
use Exception;

abstract class Enum {
  protected static $instances;
  protected static $reflection;

  public $name;
  public $value;

  /**
   * @param string $name
   * @param string $value
   */
  public function __construct($name, $value) {
    $this->name = $name;
    $this->value = $value;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @return ReflectionClass
   */
  protected static function getReflectionClass() {
    if (!is_array(self::$reflection)) {
      self::$reflection = [];
    }

    $class = get_called_class();

    if (!isset(self::$reflection[$class])) {
      self::$reflection[$class] = new ReflectionClass($class);
    }

    return self::$reflection[$class];
  }

  /**
   * @return array
   */
  public static function getConstants() {
    $reflection = static::getReflectionClass();
    return $reflection->getConstants();
  }

  /**
   * @param string $value
   * @return self|null
   */
  public static function byValue($value) {
    $constants = static::getConstants();
    foreach ($constants as $name => $constValue) {
      if ($value == $constValue) {
        return static::$name();
      }
    }
    return null;
  }

  /**
   * @param $name
   * @param $arguments
   * @return static
   * @throws Exception
   */
  public static function __callStatic($name, $arguments) {
    if (!defined("static::".$name)) {
      throw new Exception("Unknown enum '".$name."'");
    }

    if (!is_array(static::$instances)) {
      static::$instances = [];
    }

    if (!isset(static::$instances[$name])) {
      static::$instances[$name] = new static($name, constant("static::".$name));
    }

    return static::$instances[$name];
  }
}