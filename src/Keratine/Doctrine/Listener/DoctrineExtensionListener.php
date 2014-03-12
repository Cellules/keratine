<?php
namespace Keratine\Doctrine\Listener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Silex\Application;

class DoctrineExtensionListener implements EventSubscriberInterface
{
    protected $app;

    /**
     * Constructor.
     *
     * @param Application $app An Application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onLateKernelRequest(GetResponseEvent $event)
    {
        $translatable = $this->app['gedmo.listener.translatable'];
        $translatable->setTranslatableLocale($event->getRequest()->getLocale());
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $securityContext = $this->app['security'];
        if (null !== $securityContext && null !== $securityContext->getToken() && $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->app['gedmo.listener.loggable']->setUsername($securityContext->getToken()->getUsername());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onLateKernelRequest', -10),
            KernelEvents::REQUEST => array('onKernelRequest'),
        );
    }
}