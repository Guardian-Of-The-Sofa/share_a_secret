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
}