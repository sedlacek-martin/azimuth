<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Mailer;

use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigSwiftMailer implements MailerInterface
{
    protected $locale;

    protected $locales;

    protected $mailer;

    protected $router;

    protected $twig;

    protected $requestStack;

    protected $parameters;

    protected $fromEmail;

    protected $siteName;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param array                 $parameters
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        array $parameters
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;

        $this->locales = $parameters['locales'];
        $this->fromEmail = $parameters['from_email'];
        $this->siteName = $parameters['site_name'];
        $this->locale = $parameters['locale'];
        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }
    }

    /**
     * @param UserInterface $user
     */
    public function sendAccountCreatedMessageToUser(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_created_user'];

        $context = [
            'user' => $user,
            'cocorico_site_name' => $this->parameters['site_name'],
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     */
    public function sendResettingEmailMessageToUser(UserInterface $user)
    {
        $template = $this->parameters['templates']['forgot_password_user'];
        $password_reset_link = $this->router->generate(
            'cocorico_user_resetting_reset',
            ['token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context = [
            'user' => $user,
            'password_reset_link' => $password_reset_link,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     */
    public function sendAccountCreationConfirmationMessageToUser(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_creation_confirmation_user'];
        $url = $this->router->generate(
            'cocorico_user_register_confirmation',
            ['token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $context = [
            'user' => $user,
            'confirmationUrl' => $url,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     */
    public function sendAccountTrusted(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_trusted'];
        $context = [];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     */
    public function sendWaitingForTrusted(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_waiting_trusted'];
        $context = [
            'user' => $user,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param UserInterface $user
     */
    public function sendAccountReactivated(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_reactivated'];
        $context = [
            'user' => $user,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendAccountDeleted(UserInterface $user)
    {
        $template = $this->parameters['templates']['account_deleted'];
        $context = [
            'user' => $user,
        ];

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    public function sendUserInvited(string $email)
    {
        $template = $this->parameters['templates']['user_invite'];
        $context = [];

        $this->sendMessage($template, $context, $this->fromEmail, $email);
    }

    public function sendTest(string $email)
    {
        $template = $this->parameters['templates']['test'];
        $context = [];

        $this->sendMessage($template, $context, $this->fromEmail, $email);
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;

        if (isset($context['user'])) {
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
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
                ->setFrom($fromEmail, $this->siteName)
                ->setTo($toEmail);

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
