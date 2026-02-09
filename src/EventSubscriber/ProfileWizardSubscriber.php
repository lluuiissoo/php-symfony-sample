<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\ProfileRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileWizardSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private ProfileRepository $profileRepo,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $allowedRoutes = ['app_profile_wizard', 'app_logout', 'app_login', 'app_register', '_wdt', '_profiler'];
        if (in_array($route, $allowedRoutes, true)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $profile = $this->profileRepo->findOneBy(['user' => $user]);
        if ($profile === null || !$profile->isWizardCompleted()) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_profile_wizard'),
            ));
        }
    }
}
