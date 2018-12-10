<?php
namespace Dot\Crud\System\Database;

use Dot\Crud\Running\Record\Condition\Condition;
use Dot\Crud\Running\Record\Condition\AndCondition;
use Dot\Crud\Running\Record\Condition\ColumnCondition;
use Dot\Crud\Running\Record\Condition\NoCondition;
use Dot\Crud\Running\Record\Condition\NotCondition;
use Dot\Crud\Running\Record\Condition\OrCondition;
use Dot\Crud\Running\Record\Condition\SpatialCondition;
use Dot\Crud\System\Column\Reflection\ReflectedColumn;

class ConditionsBuilder {

    const DEFAULT_DRIVER    = 'mysql';

    private $_driver = null;

    private function _getConditionSql(Condition $condition, array &$arguments)   {
        if ($condition instanceof AndCondition) {
            return $this->_getAndConditionSql($condition, $arguments);
        }
        if ($condition instanceof OrCondition) {
            return $this->_getOrConditionSql($condition, $arguments);
        }
        if ($condition instanceof NotCondition) {
            return $this->_getNotConditionSql($condition, $arguments);
        }
        if ($condition instanceof ColumnCondition) {
            return $this->_getColumnConditionSql($condition, $arguments);
        }
        throw new \Exception('Unknown Condition: ' . get_class($condition));
    }

    private function _getAndConditionSql(AndCondition $and, array &$arguments)  {
        $parts = [];
        foreach ($and->getConditions() as $condition) {
            $parts[] = $this->_getConditionSql($condition, $arguments);
        }
        return '(' . implode(' AND ', $parts) . ')';
    }

    private function _getOrConditionSql(OrCondition $or, array &$arguments)  {
        $parts = [];
        foreach ($or->getConditions() as $condition) {
            $parts[] = $this->_getConditionSql($condition, $arguments);
        }
        return '(' . implode(' OR ', $parts) . ')';
    }

    private function _getNotConditionSql(NotCondition $not, array &$arguments)   {
        $condition = $not->getCondition();
        return '(NOT ' . $this->_getConditionSql($condition, $arguments) . ')';
    }

    private function _quoteColumnName(ReflectedColumn $column)  {
        return '"' . $column->getName() . '"';
    }

    private function _escapeLikeValue($value = null)    {
        return addcslashes($value, '%_');
    }

    private function _getColumnConditionSql(ColumnCondition $condition, array &$arguments)  {
        $column = $this->_quoteColumnName($condition->getColumn());
        $operator = $condition->getOperator();
        $value = $condition->getValue();
        switch ($operator) {
            case 'cs':
                $sql = "$column LIKE ?";
                $arguments[] = '%' . $this->_escapeLikeValue($value) . '%';
                break;
            case 'sw':
                $sql = "$column LIKE ?";
                $arguments[] = $this->_escapeLikeValue($value) . '%';
                break;
            case 'ew':
                $sql = "$column LIKE ?";
                $arguments[] = '%' . $this->_escapeLikeValue($value);
                break;
            case 'eq':
                $sql = "$column = ?";
                $arguments[] = $value;
                break;
            case 'lt':
                $sql = "$column < ?";
                $arguments[] = $value;
                break;
            case 'le':
                $sql = "$column <= ?";
                $arguments[] = $value;
                break;
            case 'ge':
                $sql = "$column >= ?";
                $arguments[] = $value;
                break;
            case 'gt':
                $sql = "$column > ?";
                $arguments[] = $value;
                break;
            case 'bt':
                $parts = explode(',', $value, 2);
                $count = count($parts);
                if ($count == 2) {
                    $sql = "($column >= ? AND $column <= ?)";
                    $arguments[] = $parts[0];
                    $arguments[] = $parts[1];
                } else {
                    $sql = "FALSE";
                }
                break;
            case 'in':
                $parts = explode(',', $value);
                $count = count($parts);
                if ($count > 0) {
                    $qmarks = implode(',', str_split(str_repeat('?', $count)));
                    $sql = "$column IN ($qmarks)";
                    for ($i = 0; $i < $count; $i++) {
                        $arguments[] = $parts[$i];
                    }
                } else {
                    $sql = "FALSE";
                }
                break;
            case 'is':
                $sql = "$column IS NULL";
                break;
        }
        return $sql;
    }

    private function _getSpatialFunctionName($operator = null)  {
        switch ($operator) {
            case 'co':return 'ST_Contains';
            case 'cr':return 'ST_Crosses';
            case 'di':return 'ST_Disjoint';
            case 'eq':return 'ST_Equals';
            case 'in':return 'ST_Intersects';
            case 'ov':return 'ST_Overlaps';
            case 'to':return 'ST_Touches';
            case 'wi':return 'ST_Within';
            case 'ic':return 'ST_IsClosed';
            case 'is':return 'ST_IsSimple';
            case 'iv':return 'ST_IsValid';
        }
    }

    private function _hasSpatialArgument($operator = null)  {
        return in_array($operator, ['ic', 'is', 'iv']) ? false : true;
    }

    private function _getSpatialFunctionCall($functionName = null, $column = null, $hasArgument = false)    {
        switch ($this->_driver) {
            default:
        }
    }

    private function _getSpatialConditionSql(ColumnCondition $condition, array &$arguments) {
        $column = $this->_quoteColumnName($condition->getColumn());
        $operator = $condition->getOperator();
        $value = $condition->getValue();
        $functionName = $this->_getSpatialFunctionName($operator);
        $hasArgument = $this->_hasSpatialArgument($operator);
        $sql = $this->_getSpatialFunctionCall($functionName, $column, $hasArgument);
        if ($hasArgument) {
            $arguments[] = $value;
        }
        return $sql;
    }

    public function __construct($driver = null)   {
        $this->_driver = $driver;
    }

    public function getWhereClause(Condition $condition, array &$arguments) {
        if ($condition instanceof NoCondition) {
            return '';
        }
        return ' WHERE ' . $this->_getConditionSql($condition, $arguments);
    }

}