<?php
namespace Keratine\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * GeoListener
 *
 * @author Olivier Chauvel <olivier@generation-multiple.com>
 */
class ImageCropListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT      => 'onSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (empty($data)) {
            return;
        }

        $file = isset($data['file']) ? $data['file'] : null;

        // $left = isset($data['left']) ? $data['left'] : null;
        // $top = isset($data['top']) ? $data['top'] : null;
        // $width = isset($data['width']) ? $data['width'] : null;
        // $height = isset($data['height']) ? $data['height'] : null;

        $event->setData($file);
    }

    /**
     * {@inheritdoc}
     */
    public function onPostSubmit(FormEvent $event)
    {        
        $form = $event->getForm();
        $data = $event->getData();
        
        $left = $form['left']->getData();
        $top = $form['top']->getData();
        $width = $form['width']->getData();
        $height = $form['height']->getData();

        $filename = $form->getParent()->getData()->getAbsolutePath();

        $this->crop($filename, $left, $top, $width, $height);
    }

    protected function crop($filename, $x, $y, $width, $height)
    {
        try {
            $output = imagecreatetruecolor($width, $height);
            $source = imagecreatefromjpeg($filename);
            imagecopyresampled($output, $source, 0, 0, $x, $y, $width, $height, $width, $height);
            imagejpeg($output, $filename, 100);
            imagedestroy($source);
            imagedestroy($output);
        }
        catch (Exception $e) {
            $form->addError(new FormError('image_crop.crop.error'));
        }
    }
}
