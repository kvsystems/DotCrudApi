<?php
namespace Dot\Crud\System\Database;

use Dot\Crud\System\Column\Reflection\ReflectedColumn;

class ColumnConverter   {

    const DEFAULT_DRIVER    = 'mysql';
    private $_driver = null;

    public function __construct($driver = null) {
        $this->_driver = $driver;
    }

    public function convertColumnValue(ReflectedColumn $column) {
        if ($column->isBinary()) {
            switch ($this->_driver) {
                default:
                    return "FROM_BASE64(?)";
            }
        }
        if ($column->isGeometry()) {
            switch ($this->_driver) {
                default:
            }
        }
        return '?';
    }

    public function convertColumnName(ReflectedColumn $column, $value = null)   {
        if ($column->isBinary()) {
            switch ($this->_driver) {
                default:
                    return "TO_BASE64($value) as $value";

            }
        }
        if ($column->isGeometry()) {
            switch ($this->_driver) {
                default:
            }
        }
        return $value;
    }

}