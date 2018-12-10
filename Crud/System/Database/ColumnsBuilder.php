<?php
namespace Dot\Crud\System\Database;

use Dot\Crud\System\Column\Reflection\ReflectedTable;
use Dot\Crud\System\Column\Reflection\ReflectedColumn;

class ColumnsBuilder    {

    const DEFAULT_DRIVER    = 'mysql';
    private $_driver    = null;
    private $_converter = null;

    private function _quoteColumnName(ReflectedColumn $column)   {
        return '"' . $column->getName() . '"';
    }

    public function __construct($driver = null)
    {
        $this->_driver = $driver;
        $this->_converter = new ColumnConverter($driver);
    }

    public function getOffsetLimit($offset = null, $limit = null) {
        if ($limit < 0 || $offset < 0) {
            return '';
        }
        switch ($this->_driver) {
            default:
                return "LIMIT $offset, $limit";
        }
    }

    public function getOrderBy(ReflectedTable $table, array $columnOrdering = [])   {
        $results = [];
        foreach ($columnOrdering as $i => list($columnName, $ordering)) {
            $column = $table->get($columnName);
            $quotedColumnName = $this->_quoteColumnName($column);
            $results[] = $quotedColumnName . ' ' . $ordering;
        }
        return implode(',', $results);
    }

    public function getSelect(ReflectedTable $table, array $columnNames = [])   {
        $results = [];
        foreach ($columnNames as $columnName) {
            $column = $table->get($columnName);
            $quotedColumnName = $this->_quoteColumnName($column);
            $quotedColumnName = $this->_converter->convertColumnName($column, $quotedColumnName);
            $results[] = $quotedColumnName;
        }
        return implode(',', $results);
    }

    public function getInsert(ReflectedTable $table, array $columnValues = [])  {
        $columns    = [];
        $values     = [];
        foreach ($columnValues as $columnName => $columnValue) {
            $column = $table->get($columnName);
            $quotedColumnName = $this->_quoteColumnName($column);
            $columns[] = $quotedColumnName;
            $columnValue = $this->_converter->convertColumnValue($column);
            $values[] = $columnValue;
        }
        $columnsSql = '(' . implode(',', $columns) . ')';
        $valuesSql = '(' . implode(',', $values) . ')';
        $outputColumn = $this->_quoteColumnName($table->getPk());
        switch ($this->_driver) {
            default:
                return "$columnsSql VALUES $valuesSql";
        }
    }

    public function getUpdate(ReflectedTable $table, array $columnValues = [])   {
        $results = [];
        foreach ($columnValues as $columnName => $columnValue) {
            $column = $table->get($columnName);
            $quotedColumnName = $this->_quoteColumnName($column);
            $columnValue = $this->_converter->convertColumnValue($column);
            $results[] = $quotedColumnName . '=' . $columnValue;
        }
        return implode(',', $results);
    }

    public function getIncrement(ReflectedTable $table, array $columnValues = [])   {
        $results = [];
        foreach ($columnValues as $columnName => $columnValue) {
            if (!is_numeric($columnValue)) {
                continue;
            }
            $column = $table->get($columnName);
            $quotedColumnName = $this->_quoteColumnName($column);
            $columnValue = $this->_converter->convertColumnValue($column);
            $results[] = $quotedColumnName . '=' . $quotedColumnName . '+' . $columnValue;
        }
        return implode(',', $results);
    }

}