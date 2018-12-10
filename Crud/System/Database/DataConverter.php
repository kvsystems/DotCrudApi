<?php
namespace Dot\Crud\System\Database;

use Dot\Crud\System\Column\Reflection\ReflectedTable;
use Dot\Crud\System\Column\Reflection\ReflectedColumn;

class DataConverter {

    const DEFAULT_DRIVER    = 'mysql';
    private $_driver = null;

    private function _convertRecordValue($conversion = null, $value = null)
    {
        switch ($conversion) {
            case 'boolean':
                return $value ? true : false;
        }
        return $value;
    }

    private function _getRecordValueConversion(ReflectedColumn $column = null) {
        if (in_array($this->_driver, [self::DEFAULT_DRIVER]) && $column->isBoolean()) {
            return 'boolean';
        }
        return 'none';
    }

    private function _convertInputValue($conversion = null, $value = null)  {
        switch ($conversion) {
            case 'base64url_to_base64':
                return str_pad(strtr($value, '-_', '+/'), ceil(strlen($value) / 4) * 4, '=', STR_PAD_RIGHT);
        }
        return $value;
    }

    private function _getInputValueConversion(ReflectedColumn $column = null)   {
        if ($column->isBinary()) {
            return 'base64url_to_base64';
        }
        return 'none';
    }

    public function __construct($driver = null) {
        $this->_driver = $driver;
    }

    public function convertRecords(ReflectedTable $table = null, array $columnNames = [], array &$records = []) {
        foreach ($columnNames as $columnName) {
            $column = $table->get($columnName);
            $conversion = $this->_getRecordValueConversion($column);
            if ($conversion != 'none') {
                foreach ($records as $i => $record) {
                    $value = $records[$i][$columnName];
                    if ($value === null) {
                        continue;
                    }
                    $records[$i][$columnName] = $this->_convertRecordValue($conversion, $value);
                }
            }
        }
    }

    public function convertColumnValues(ReflectedTable $table = null, array &$columnValues = [])    {
        $columnNames = array_keys($columnValues);
        foreach ($columnNames as $columnName) {
            $column = $table->get($columnName);
            $conversion = $this->_getInputValueConversion($column);
            if ($conversion != 'none') {
                $value = $columnValues[$columnName];
                if ($value !== null) {
                    $columnValues[$columnName] = $this->_convertInputValue($conversion, $value);
                }
            }
        }
    }

}