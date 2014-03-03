<?php
namespace Keratine\Widget;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Boolean extends AbstractWidget
{
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('icon_check', 'icon_unchecked'));

        $resolver->setDefaults(array(
            'icon_check'     => 'glyphicon-check',
            'icon_unchecked' => 'glyphicon-unchecked',
        ));

        $resolver->setAllowedTypes(array(
            'icon_check'     => 'string',
            'icon_unchecked' => 'string',
        ));
    }

	public function render($entity, $column)
	{
		$route = $this->container['request']->attributes->get('_route');
		$route = current(explode('_', $route));

        $id = $this->getEntityValue($entity, 'id');
		$value = $this->getEntityValue($entity, $column);

		$ajax_route = $route.'_ajax';

		$url = $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => (int) !$value));

		$callback = "var icon = this.querySelector('.glyphicon');
			if (icon.classList.contains('".$this->options['icon_unchecked']."')) {
                var icon_check = '".$this->options['icon_check']."'.trim().split(' ');
                for (i in icon_check) {
                    icon.classList.add(icon_check[i]);
                }
                var icon_unchecked = '".$this->options['icon_unchecked']."'.trim().split(' ');
                for (i in icon_unchecked) {
                    icon.classList.remove(icon_unchecked[i]);
                }
				this.href = '". $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => 0)) ."';
			}
			else {
                var icon_unchecked = '".$this->options['icon_unchecked']."'.trim().split(' ');
                for (i in icon_unchecked) {
                    icon.classList.add(icon_unchecked[i]);
                }
                var icon_check = '".$this->options['icon_check']."'.trim().split(' ');
                for (i in icon_check) {
                    icon.classList.remove(icon_check[i]);
                }
				this.href = '". $this->container['url_generator']->generate($ajax_route, array('id' => $id, 'column' => $column, 'value' => 1)) ."';
			}";

		if ($value) {
			return sprintf('<a class="btn btn-link ajax" href="%s" data-callback="%s"><span class="glyphicon %s"></span></a>', $url, $callback, $this->options['icon_check']);
		}
		else {
			return sprintf('<a class="btn btn-link ajax" href="%s" data-callback="%s"><span class="glyphicon %s"></span></a>', $url, $callback, $this->options['icon_unchecked']);
		}
	}
}