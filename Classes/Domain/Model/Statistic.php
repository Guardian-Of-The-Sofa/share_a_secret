<?php

namespace Hn\HnShareSecret\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Statistic extends AbstractEntity
{
    /**
     * @var \Hn\HnShareSecret\Domain\Model\Secret
     */
    protected $secret;
    /**
     * @var int
     */
    protected $created;
    /**
     * @var int
     */
    protected $read;
    /**
     * @var int
     */
    protected $deleted;

    public function __construct(int $read = null, int $deleted = null)
    {
        $this->read = $read;
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param mixed $secret
     */
    public function setSecret($secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return int
     */
    public function getRead(): int
    {
        return $this->read;
    }

    /**
     * @param int $read
     */
    public function setRead(int $read): void
    {
        $this->read = $read;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted(int $deleted): void
    {
        $this->deleted = $deleted;
    }

}