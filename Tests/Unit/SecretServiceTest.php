<?php

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use Hn\HnShareSecret\Service\SecretService;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;

class SecretServiceTest extends TestCase
{
    /* @var SecretRepository|\PHPUnit\Framework\MockObject\MockObject */
    protected $secretRepository;

    /* @var SecretService */
    protected $secretService;

    /* @var callable */
    protected $secretRepositoryAddCallback;

    /* @var Secret[] */
    protected $secrets = [];

    public function dummyValuesProvider()
    {
        return [
            ['', 'test'],
            ['test', ''],
            ['', ''],
        ];
    }

    public function setUp()
    {
        $this->secretRepository = $this->createMock(SecretRepository::class);
        $this->secretService = new SecretService($this->secretRepository);

        $this->secretRepository
            ->method('add')
            ->willReturnCallback(function (Secret $secret) {
                $this->secrets[$secret->getIndexHash()] = $secret;
            });

        $this->secretRepository
            ->method('findOneByIndexHash')
            ->willReturnCallback(function ($indexHash) {
                return $this->secrets[$indexHash] ?? null;
            });
    }

    /**
     * @dataProvider dummyValuesProvider
     * @test
     * @param string $password
     * @param string $linkHash
     * @throws InvalidArgumentValueException
     */
    public function createPasswordWithEmptyValuesFails(string $password, string $linkHash)
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->secretService->createPassword($password, $linkHash);
    }

    /**
     * @param string $password
     * @param string $linkHash
     * @throws InvalidArgumentValueException
     * @dataProvider dummyValuesProvider
     * @test
     */
    public function createIndexHashWithEmptyValuesFails(string $password, string $linkHash)
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->secretService->createIndexHash($password, $linkHash);
    }

    /**
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws Exception
     * @test
     */
    public function messageCanBeDecrypted()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';
        $this->secretRepository->expects($this->once())->method('save');
        $linkHash = $this->secretService->createSecret($message, $userPassword);
        $this->assertCount(1, $this->secrets);
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->assertEquals($message, $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash));
    }

    /**
     * @throws Exception
     * @test
     * TODO: Test vielleicht unschön?
     */
    public function messageGetsEncrypted()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';
        $linkHash = $this->secretService->createSecret($message, $userPassword);
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->assertNotEquals($message, $secret->getMessage());
    }
}
