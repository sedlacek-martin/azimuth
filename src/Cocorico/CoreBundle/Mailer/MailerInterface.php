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

interface MailerInterface
{
    /**
     * email is sent when a listing is activated
     *
     * @param Listing $listing
     *
     * @return void
     */
    public function sendListingActivatedMessageToOfferer(Listing $listing);

    /**
     * email is sent to admin
     *
     * @param string $subject
     * @param string $message
     *
     * @return void
     */
    public function sendMessageToAdmin($subject, $message);
}
