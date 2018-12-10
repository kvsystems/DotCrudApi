<?php
namespace Dot\Crud\Running\Record\Condition;

class NoCondition extends Condition {

    public function thisAnd(Condition $condition = null)  {
        return $condition;
    }

    public function thisOr(Condition $condition = null)   {
        return $condition;
    }

    public function thisNot()  {
        return $this;
    }

}