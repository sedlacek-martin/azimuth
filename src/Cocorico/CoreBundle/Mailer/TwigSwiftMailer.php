<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Mailer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

class TwigSwiftMailer implements MailerInterface
{
    const TRANS_DOMAIN = 'cocorico_mail';

    protected $mailer;

    protected $router;

    protected $twig;

    protected $requestStack;

    protected $translator;

    protected $timeUnit;

    protected $timeUnitIsDay;

    protected $locale;

    /** @var  array locales */
    protected $locales;

    protected $timezone;

    protected $templates;

    protected $fromEmail;

    protected $bccEmail;

    protected $adminEmail;

    protected $siteName;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param Translator            $translator
     * @param array                 $parameters
     * @param array                 $templates
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        Translator $translator,
        array $parameters,
        array $templates
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->translator = $translator;

        /** parameters */
        $parameters = $parameters['parameters'];

        $this->fromEmail = $parameters['cocorico_from_email'];
        $this->adminEmail = $parameters['cocorico_contact_email'];
        $this->siteName = $parameters['cocorico_site_name'];
        $this->bccEmail = $parameters['cocorico_bcc_email'];

        $this->timeUnit = $parameters['cocorico_time_unit'];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;

        $this->locales = $parameters['cocorico_locales'];
        $this->locale = $parameters['cocorico_locale'];
        $this->timezone = $parameters['cocorico_time_zone'];

        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }

        $this->templates = $templates['templates'];
    }

    /**
     * @param Listing $listing
     */
    public function sendListingActivatedMessageToOfferer(Listing $listing)
    {
        $user = $listing->getUser();
        $template = $this->templates['listing_activated_offerer'];

        $context = [
            'user' => $user,
            'listing' => $listing,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param User $user
     */
    public function sendUserExpireNotification(User $user): void
    {
        $template = $this->templates['user_expire_notification'];

        $reconfirmUrl = $this->router->generate('cocorico_user_reconfirm', [
            'token' => $user->getUniqueHash(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'reconfirmUrl' => $reconfirmUrl,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendUserExpired(User $user)
    {
        $template = $this->templates['user_expired'];

        $reconfirmUrl = $this->router->generate('cocorico_user_reconfirm', [
            'token' => $user->getUniqueHash(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $user,
            'reconfirmUrl' => $reconfirmUrl,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendUserDeleted(User $user)
    {
        $template = $this->templates['user_deleted'];

        $context = [
            'user' => $this->locale,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendActivatorNotification(User $user, int $activationCount, int $reconfirmCount, int $contactCount): void
    {
        $template = $this->templates['activator_notification'];

        $context = [
            'user' => $user,
            'new_activations_count' => $activationCount,
            'new_reconfirm_count' => $reconfirmCount,
            'contact_new' => $contactCount,

        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendFacilitatorNotification(User $user, int $postValidationCount, int $messageValidationCount, int $postNewCount, int $contactCount): void
    {
        $template = $this->templates['facilitator_notification'];

        $context = [
            'user' => $user,
            'post_validations' => $postValidationCount,
            'message_validations' => $messageValidationCount,
            'post_new' => $postNewCount,
            'contact_new' => $contactCount,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendSuperAdminNotification(User $user, int $contactCount): void
    {
        $template = $this->templates['super_admin_notification'];

        $context = [
            'user' => $user,
            'contact_new' => $contactCount,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendListingExpireSoonNotification(Listing $listing)
    {
        $template = $this->templates['listing_expire_soon'];

        $context = [
            'listing' => $listing,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $listing->getUser()->getEmail());
    }

    public function sendListingExpired($listing)
    {
        $template = $this->templates['listing_expired'];

        $context = [
            'listing' => $listing,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $listing->getUser()->getEmail());
    }

    public function sendMessageToAdmin($subject, $message)
    {
        $template = $this->templates['admin_message'];

        $context = [
            'user_locale' => $this->locale,
            'subject' => $subject,
            'admin_message' => $message,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $this->adminEmail);
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param array  $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $user = null;
        $context['trans_domain'] = self::TRANS_DOMAIN;

        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;
        $context['user_timezone'] = $this->timezone;

        if (isset($context['user'])) {//user receiving the email
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
            $context['user_timezone'] = $user->getTimeZone();
        }

        if (isset($context['listing'])) {
            /** @var Listing $listing */
            $listing = $context['listing'];
            $translations = $listing->getTranslations();
            if ($translations->count() && isset($translations[$context['user_locale']])) {
                $slug = $translations[$context['user_locale']]->getSlug();
                $title = $translations[$context['user_locale']]->getTitle();
            } else {
                $slug = $listing->getSlug();
                $title = $listing->getTitle();
            }
            $context['listing_public_url'] = $this->router->generate(
                'cocorico_listing_show',
                [
                    '_locale' => $context['user_locale'],
                    'slug' => $slug,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $context['listing_title'] = $title;
        }

        try {
            /** @var \Twig_Template $template */
            $template = $this->twig->loadTemplate($templateName);
            $context = $this->twig->mergeGlobals($context);

            $subject = $template->renderBlock('subject', $context);
            $context['message'] = $template->renderBlock('message', $context);

            $textBody = $template->renderBlock('body_text', $context);
            $htmlBody = $template->renderBlock('body_html', $context);

            $message = (new \Swift_Message($subject))
                ->setFrom($fromEmail)
                ->setTo($toEmail)
                ->setBcc($this->bccEmail);

            if (!empty($htmlBody)) {
                $message
                    ->setBody($htmlBody, 'text/html')
                    ->addPart($textBody, 'text/plain');
            } else {
                $message->setBody($textBody);
            }

            $this->mailer->send($message);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
