<?php

namespace Hn\HnShareSecret\Domain\Repository;

use Hn\HnShareSecret\Domain\Model\Secret;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class SecretRepository
 * @package Hn\HnShareSecret\Domain\Repository
 * @method Secret findOneByIndexHash(string $indexHash)
 */
class SecretRepository extends Repository
{
    public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
    }
}