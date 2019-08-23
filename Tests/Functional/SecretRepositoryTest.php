<?php

namespace Hn\HnShareSecret\Tests\Functional;

use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class SecretRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/hn_share_secret'
    ];

    private $objectManager;
    /**
     * @var SecretRepository
     */
    private $secretRepository;

    public function setUp()
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->secretRepository = $this->objectManager->get(SecretRepository::class);
    }

    public function testSave()
    {
        $secret = new Secret('a', 'a');
        $this->secretRepository->add($secret);
        $this->secretRepository->save();
        $this->assertNotEquals(0, $this->secretRepository->countAll());
    }

    public function testFindOneByIndexHash()
    {
        /** @var Secret[] $secrets */
        $secrets = [
            new Secret('a', 'a'),
            new Secret('b', 'b'),
            new Secret('c', 'c'),
        ];

        foreach ($secrets as $secret){
            $this->secretRepository->add($secret);
            $this->secretRepository->save();
        }

        foreach ($secrets as $secret){
            $foundSecret = $this->secretRepository->findOneByIndexHash($secret->getIndexHash());
            $this->assertSame($secret, $foundSecret);
        }
    }
}
