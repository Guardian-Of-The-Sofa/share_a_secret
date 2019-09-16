<?php

namespace Hn\ShareASecret\Service;

use Hn\ShareASecret\Domain\Model\EventLog;
use Hn\ShareASecret\Domain\Model\Secret;
use Hn\ShareASecret\Domain\Repository\EventLogRepository;

class EventLogService
{
    /**
     * @var EventLogRepository
     */
    private $eventLogRepository;

    public function __construct(EventLogRepository $eventLogRepository)
    {
        $this->eventLogRepository = $eventLogRepository;
    }

    public function log(EventLog $eventLog)
    {
        $this->eventLogRepository->add($eventLog);
        $this->eventLogRepository->save();
    }

    public function logCreate(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::CREATE, $secret));
    }

    public function logSuccess(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::SUCCESS, $secret));
    }

    public function logDelete(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::DELETE, $secret));
    }

    public function logRequest(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::REQUEST, $secret));
    }

    public function logNotFound(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::NOTFOUND, $secret));
    }
}