<?php

namespace Hn\HnShareSecret\Domain\Repository;

use Hn\HnShareSecret\Domain\Model\Secret;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class SecretRepository
 * @package Hn\HnShareSecret\Domain\Repository
 * @method Secret findOneByLinkHash(string $linkHash)
 */
class SecretRepository extends Repository
{

}