<?php

namespace Hn\HnShareSecret\Service;

use Hn\HnShareSecret\Domain\Model\EventLog;
use Hn\HnShareSecret\Domain\Repository\EventLogRepository;

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

    public function logCreate()
    {
        $this->log(new EventLog(EventLog::CREATE));
    }

    public function logSuccess()
    {
        $this->log(new EventLog(EventLog::SUCCESS));
    }

    public function logDelete()
    {
        $this->log(new EventLog(EventLog::DELETE));
    }

    public function logRequest()
    {
        $this->log(new EventLog(EventLog::REQUEST));
    }

    public function logNotFound()
    {
        $this->log(new EventLog(EventLog::NOTFOUND));
    }
}