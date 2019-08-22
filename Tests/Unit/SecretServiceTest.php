<?php


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use Hn\HnShareSecret\Service\SecretService;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;

/* @var Secret $entity */
$entity = null;

class SecretServiceTest extends TestCase
{
    /* @var SecretRepository|\PHPUnit\Framework\MockObject\MockObject */
    protected $secretRepository;

    /* @var SecretService */
    protected $secretService;

    /* @var callable */
    protected $secretRepositoryAddCallback;

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
        global $entity;
        $this->secretRepositoryAddCallback = function($secret) use (&$entity){
            $this->assertInstanceOf(Secret::class, $secret);
            $entity = $secret;
        };
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
    public function messagesCanBeDecrypted()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';

        /** @var Secret $entity */
        $entity = null;
        $this->secretRepository->expects($this->once())
            ->method('add')
            ->willReturnCallback(function ($parameter) use (&$entity) {
                $this->assertInstanceOf(Secret::class, $parameter);
                $entity = $parameter;
            });
        $this->secretRepository->expects($this->once())->method('save');

        $linkHash = $this->secretService->createSecret($message, $userPassword);

        $this->secretRepository->expects($this->once())
            ->method('findOneByIndexHash')
            ->with($entity->getIndexHash())
            ->willReturn($entity);

        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->assertEquals($message, $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash));
    }

    /**
     * TODO: Test ist unschön, besser machen.
     * TODO: Test enthält doppelten Code. Hier sehe ich keine Lösung,
     *       da anonyme Funktionen weder Superglobals, noch $this, noch ein self vom Eltern-Scope erben dürfen.
     * @throws Exception
     * @test
     */
    public function messageGetsEncrypted()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';
        /* @var Secret $entity */
        $entity = null;
        $this->secretRepository->expects($this->once())
            ->method('add')
            ->willReturnCallback(function ($secret) use (&$entity) {
                $this->assertInstanceOf(Secret::class, $secret);
                $entity = $secret;
            });
        $this->secretService->createSecret($message, $userPassword);
        $this->assertNotEquals($message, $entity->getMessage());
    }
}
