<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Model;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\MessageBundle\MessageBuilder\NewThreadMessageBuilder;
use Cocorico\MessageBundle\MessageBuilder\ReplyMessageBuilder;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\MessageBundle\EntityManager\MessageManager as FOSMessageManager;
use FOS\MessageBundle\EntityManager\ThreadManager as FOSThreadManager;
use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\MessageBundle\Model\ThreadInterface;

/**
 * Default ORM ThreadManager.
 *
 *
 */
class ThreadManager
{
    protected $fosThreadManager;
    protected $fosMessageManager;
    protected $mailer;
    public $maxPerPage;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor.
     *
     * @param FOSThreadManager  $fosThreadManager
     * @param FOSMessageManager $fosMessageManager
     * @param TwigSwiftMailer   $mailer
     * @param integer           $maxPerPage
     */
    public function __construct(
        FOSThreadManager $fosThreadManager,
        FOSMessageManager $fosMessageManager,
        EntityManagerInterface $em,
        TwigSwiftMailer $mailer,
        $maxPerPage
    ) {
        $this->fosThreadManager = $fosThreadManager;
        $this->fosMessageManager = $fosMessageManager;
        $this->mailer = $mailer;
        $this->maxPerPage = $maxPerPage;
        $this->em = $em;
    }

    /**
     * Finds not deleted threads for a participant,
     * containing at least one message not written by this participant,
     * ordered by last message not written by this participant in reverse order.
     * also checks if the threads are not connected to any of the bookings
     * In one word: an inbox.
     *
     * Doctrine bugs:
     *
     * @link https://github.com/doctrine/doctrine2/pull/1220
     * @link http://www.doctrine-project.org/jira/browse/DDC-2890
     * @link https://github.com/doctrine/doctrine2/pull/1151
     *
     *
     * @param ParticipantInterface $participant
     * @param integer $page
     * @return Paginator object
     */
    public function getListingInboxThreads(ParticipantInterface $participant, $page = 1): Paginator
    {
        $queryBuilder = $this->em->getRepository(Thread::class)
            ->createQueryBuilder('t')
            ->innerJoin('t.metadata', 'tm')
            ->innerJoin('t.messages', 'm')
            ->innerJoin('m.sender', 'ms')
            ->innerJoin('tm.participant', 'p')
            // the participant is in the thread participants
            ->andWhere('p.id = :user_id')
            ->setParameter('user_id', $participant->getId())
            // the thread does not contain spam or flood
            ->andWhere('t.isSpam = :isSpam')
            ->setParameter('isSpam', false)
            // the thread is not deleted by this participant
            ->andWhere('tm.isDeleted = :isDeleted')
            ->setParameter('isDeleted', false)
            // sort by date of last message
            ->orderBy('IFNULL(tm.lastMessageDate, tm.lastParticipantMessageDate)', 'DESC')
            ->groupBy('t.id')
            ->having('sum(case when ms.id = :user_id then 1 else m.verified end) > 0');

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        //Arg fetchJoinCollection setted to false due to Doctrine bug
        //todo: resolve otherwise this problem
        $paginator = new Paginator($query, false);

       // $paginator->setUseOutputWalkers(true);
        return $paginator;
    }

    /**
     * Get user reply rate and average delay in seconds
     *
     * @param ParticipantInterface $user
     * @return array 'reply_rate' => rate, 'reply_delay' => duration in seconds
     */
    public function getReplyRateAndDelay(ParticipantInterface $user)
    {
        $replyRate = $replyDelay = 0;
        /** @var Thread[] $inboxThreads */
        $inboxThreads = $this->fosThreadManager->findParticipantInboxThreads($user);
        $nbInBox = count($inboxThreads);

        if ($nbInBox) {
            /** @var Thread[] $sendboxThreads */
            $sendboxThreads = $this->fosThreadManager->findParticipantSentThreads($user);
            $nbSendBox = count($sendboxThreads);
            $replyRate = $nbSendBox / $nbInBox;

            foreach ($sendboxThreads as $cpt => $sendboxThread) {
                $threadMetaData = $sendboxThread->getMetadataForParticipant($user);
                /** @var \DateTime $lastReplyDate */
                $lastReplyDate = $threadMetaData->getLastParticipantMessageDate();
                /** @var  \DateTime $lastMsgDate */
                $lastMsgDate = $threadMetaData->getLastMessageDate();
                if ($lastReplyDate && $lastMsgDate) {
                    //todo: check when $lastMsgDate > $lastReplyDate
                    $replyDelay += max($lastReplyDate->getTimestamp() - $lastMsgDate->getTimestamp(), 0);
                }
            }

            if ($nbSendBox) {
                $replyDelay = $replyDelay / $nbSendBox;
            }

        }

        return array(
            "reply_rate" => $replyRate,
            "reply_delay" => round($replyDelay),
        );
    }


}
