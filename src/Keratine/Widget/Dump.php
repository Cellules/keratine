<?php
namespace Keratine\Widget;

class Dump extends AbstractWidget
{
    public function render($entity, $column)
    {
        $value = $this->getEntityValue($entity, $column);

        return print_r($value, true);
    }
}