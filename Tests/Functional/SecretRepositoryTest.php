<?php

namespace Hn\ShareASecret\Tests\Functional;

use Hn\ShareASecret\Domain\Model\Secret;
use Hn\ShareASecret\Domain\Repository\SecretRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class SecretRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/share_a_secret'
    ];

    private $objectManager;

    /* @var SecretRepository */
    private $secretRepository;

    /* @var Secret[] */
    private $secrets;

    public function setUp()
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->secretRepository = $this->objectManager->get(SecretRepository::class);
        $this->secrets = [
            new Secret('a', 'a'),
            new Secret('b', 'b'),
            new Secret('c', 'c'),
        ];
    }

    /**
     * @test
     */
    public function testSave()
    {
        $secret = new Secret('a', 'a');
        $this->secretRepository->add($secret);
        $this->secretRepository->save();
        $this->assertEquals(1, $this->secretRepository->countAll());
    }

    /**
     * @test
     */
    public function saveMultiple()
    {
        foreach ($this->secrets as $secret) {
            $this->secretRepository->add($secret);
            $this->secretRepository->save();
        }
        $this->assertEquals(count($this->secrets), $this->secretRepository->countAll());
    }

    /**
     * @test
     */
    public function testFindOneByIndexHash()
    {
        foreach ($this->secrets as $secret) {
            $this->secretRepository->add($secret);
        }
        $this->secretRepository->save();

        foreach ($this->secrets as $secret) {
            $foundSecret = $this->secretRepository->findOneByIndexHash($secret->getIndexHash());
            $this->assertSame($secret, $foundSecret);
        }
    }

    /**
     * @test
     */
    public function testDelete()
    {
        foreach ($this->secrets as $secret) {
            $this->secretRepository->add($secret);
        }
        $this->secretRepository->save();
        $this->assertEquals(count($this->secrets), $this->secretRepository->countAll());
        foreach ($this->secrets as $secret){
            $this->secretRepository->deleteSecret($secret);
            $foundSecret = $this->secretRepository->findOneByIndexHash($secret->getIndexHash());
            $this->assertNull($foundSecret);
        }
        $this->assertEquals(0, $this->secretRepository->countAll());
    }
}
