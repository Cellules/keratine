<?php
namespace Keratine\Widget;

class Boolean extends AbstractWidget
{
	public function render($entity, $column)
	{
		$route = $this->container['request']->attributes->get('_route');
		$route = current(explode('_', $route));

        $id = $this->getEntityValue($entity, 'id');
		$value = $this->getEntityValue($entity, $column);

		$ajax_route = $route.'_ajax';

		$url = $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => (int) !$value));

		$callback = "var icon = this.querySelector('.glyphicon');
			if (icon.classList.contains('glyphicon-unchecked')) {
				icon.classList.add('glyphicon-check');
				icon.classList.remove('glyphicon-unchecked');
				this.href = '". $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => 0)) ."';
			}
			else {
				icon.classList.add('glyphicon-unchecked');
				icon.classList.remove('glyphicon-check');
				this.href = '". $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => 1)) ."';
			}";

		if ($value) {
			return sprintf('<a class="btn btn-link ajax" href="%s" data-callback="%s"><span class="glyphicon glyphicon-check"></span></a>', $url, $callback);
		}
		else {
			return sprintf('<a class="btn btn-link ajax" href="%s" data-callback="%s"><span class="glyphicon glyphicon-unchecked"></span></a>', $url, $callback);
		}
	}
}