<?php
namespace Keratine\Widget;

class Collection extends AbstractWidget
{
    public function render($entity, $column)
    {
        $collection = $this->getEntityValue($entity, $column);

        return $this->renderRecursive($collection);
    }

    protected function renderRecursive($collection)
    {
        if (current($collection) && $this->isAssociativeArray(current($collection))) {
            return $this->renderTable($collection);
        }
        else {
            return $this->renderList($collection);
        }
    }

    protected function renderList($collection)
    {
        $html = '<ul>';
        foreach ($collection as $item) {
            $html .= '<li>' . $item . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    protected function renderTable($collection)
    {
        $html = '<table class="table unstyled">';
        foreach ($collection as $item) {
            $html .= '<tbody>';
            foreach ($item as $key => $value) {
                $html .= '<tr>';
                $html .= '<th>' . $key . '</th>';
                $html .= '<td>' . $value . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }
        $html .= '</table>';
        return $html;
    }

    protected function isAssociativeArray($array)
    {
        return !(is_array($array) && array_keys($array) === range(0, count($array) - 1));
    }
}