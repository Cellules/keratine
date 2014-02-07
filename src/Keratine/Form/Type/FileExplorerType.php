<?php
namespace Keratine\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FileExplorerType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'file_explorer';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return 'text';
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'explorer_path',
            'path',
            'only_mimes',
            'ui',
            'ui_options',
        ));

        $resolver->setDefaults(array(
            'explorer_path' => 'finder'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $params = array();

        if (!empty($options['path'])) {
            $params['path'] = $options['path'];
        }

        if (!empty($options['only_mimes'])) {
            $params['onlyMimes'] = $options['only_mimes'];
        }

        if (!empty($options['ui'])) {
            $params['ui'] = $options['ui'];
        }

        if (!empty($options['ui_options'])) {
            $params['uiOptions'] = $options['ui_options'];
        }

        $view->vars['explorer_path'] = $options['explorer_path'];

        $view->vars['explorer_params'] = $params;
    }
}