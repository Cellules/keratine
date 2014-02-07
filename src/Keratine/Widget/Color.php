<?php
namespace Keratine\Widget;

class Color extends AbstractWidget
{
    public function render($entity, $column)
    {
        $color = $this->getEntityValue($entity, $column);

        return sprintf('<span class="widget color" style="color: %1$s;">%1$s<span>', $color);
    }
}