<?php

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use Hn\HnShareSecret\Exceptions\SecretNotFoundException;
use Hn\HnShareSecret\Service\SecretService;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

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
            [' ', ''],
            ['', ' '],
            ["\t", ""],
            ["", "\t"],
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
     * @test
     * @dataProvider dummyValuesProvider
     * @param string $message
     * @param string $userPassword
     * @throws EnvironmentIsBrokenException
     * @throws InvalidArgumentValueException
     * @throws IllegalObjectTypeException
     */
    public function createSecretWithEmptyOrWhitespaceValuesFails(string $message, string $userPassword)
    {
        $this->expectException(InvalidArgumentValueException::class);
        $this->secretService->createSecret($message, $userPassword);
    }

    /**
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws Exception
     * @test
     */
    public function getDecryptedMessageSucceedsOnValidSecret()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';
        $this->secretRepository->expects($this->once())->method('save');
        $linkHash = $this->secretService->createSecret($message, $userPassword);
        $this->assertCount(1, $this->secrets);
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->assertEquals($message, $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash));
    }

    public function invalidInputProvider()
    {
        return [
            ['a', 'b'],
            ['' , 'b'],
            ['a', ''],
            ['' , ''],
        ];
    }

    /**
     * @test
     * @dataProvider invalidInputProvider
     * @param string $userPassword
     * @param string $linkHash
     * @throws EnvironmentIsBrokenException
     * @throws IllegalObjectTypeException
     * @throws InvalidArgumentValueException
     * @throws SecretNotFoundException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function getDecryptedMessageFailsOnValidSecretButWrongCredentials(string $userPassword, string $linkHash)
    {
        $this->expectException(SecretNotFoundException::class);
        $this->secretService->createSecret('Hello World', 'CorrectHorseBatteryStaple');
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash);
    }

    /**
     * @test
     * @throws Exception
     */
    public function messageGetsEncrypted()
    {
        $message = 'Hello World!';
        $userPassword = 'CorrectHorseBatteryStaple';
        $linkHash = $this->secretService->createSecret($message, $userPassword);
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        $this->assertNotEquals($message, $secret->getMessage());
        $this->assertEquals($message, $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash));
    }

    public function invalidNumOfCharValuesProvider()
    {
        return [
            [-10], [-5], [0], [1], [2],
        ];
    }

    /**
     * @dataProvider invalidNumOfCharValuesProvider
     * @test
     * @param int $numOfChars
     * @throws Exception
     */
    public function generateUserPasswordThrowsExceptionOnInvalidInput(int $numOfChars)
    {
        $this->expectException(RangeException::class);
        $this->secretService->generateUserPassword($numOfChars);
    }

    /**
     * @test
     * @throws Exception
     */
    public function generateUserPasswordGeneratesExactlyNchars()
    {
        for ($n = 4; $n < 100; $n++) {
            $userPassword = $this->secretService->generateUserPassword($n);
            $this->assertEquals($n, strlen($userPassword));
        }
    }

    public function invalidUserPasswordsProvider()
    {
        return [
            ['bla'],
            ['CorrectHorseBatteryStaple'],
            ['123'],
            ['123#'],
            ['asdfASDF123'],
            ['sdf#ASDF'],
            ['aasldkjfhsdfas97df98df79adf79f79d79a79a9df87aADFADFADF'],
        ];
    }

    /**
     * @dataProvider invalidUserPasswordsProvider
     * @test
     * @param $userPassword
     */
    public function userPasswordIsValidReturnsFalseOnInvalidInput($userPassword)
    {
        $this->assertFalse($this->secretService->userPasswordIsValid($userPassword));
    }

    public function validUserPasswordsProvider()
    {
        return [
            ['bla12G3#'],
            ['CorrectHorseBattery1189*Staple'],
            ['123Af+'],
            ['123#fffA'],
            ['asdfASDF123!'],
            ['sdf#ASDF0'],
            ['aasldkjfhsdfas97df98df79adf79f79d79a79a9df87aADFADFADF/'],
        ];
    }

    /**
     * @dataProvider validUserPasswordsProvider
     * @test
     * @param $userPassword
     */
    public function userPasswordIsValidReturnsTrueOnValidInput($userPassword)
    {
        $this->assertTrue($this->secretService->userPasswordIsValid($userPassword));
    }
}
