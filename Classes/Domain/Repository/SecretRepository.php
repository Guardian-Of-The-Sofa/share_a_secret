<?php

namespace Hn\HnShareSecret\Domain\Repository;

use Hn\HnShareSecret\Domain\Model\Secret;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class SecretRepository
 * @package Hn\HnShareSecret\Domain\Repository
 */
class SecretRepository extends Repository
{
    public function save()
    {
        $this->persistenceManager->persistAll();
    }

    /**
     * @param string $hash
     * @return Secret|null
     */
    public function findOneByIndexHash(string $hash): ?Secret
    {
        $query = $this->createQuery();
        $query->matching($query->equals('indexHash', $hash));
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->execute()->getFirst();
    }

    public function deleteSecret(Secret $secret)
    {
        $this->remove($secret);
        $this->save();
    }




}