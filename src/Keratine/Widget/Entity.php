<?php
namespace Keratine\Widget;

class Entity extends AbstractWidget
{
    public function render($entity, $column)
    {
        $entity = $this->getEntityValue($entity, $column);

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $data = $serializer->serialize($entity, 'json');
        $data = json_decode($data);

        return $this->renderItem($data);
    }

    public function renderItem($item)
    {
        $html = '<table class="table unstyled">';
        $html .= '<tbody>';
        foreach ((array) $item as $key => $value) {
            $html .= '<tr>';
            $html .= '<th>' . $key . '</th>';
            $html .= '<td>';
            if (is_array($value) || is_object($value)) {
                $html .= $this->renderItem($value);
            } else {
                $html .= $value;
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }
}