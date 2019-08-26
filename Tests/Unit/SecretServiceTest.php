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
    public function invalidNumOfCharsThrowsException(int $numOfChars)
    {
        $this->expectException(RangeException::class);
        $this->secretService->generateUserPassword($numOfChars);
    }

    /**
     * @test
     * @throws Exception
     */
    public function userPasswordGeneratorGeneratesExactlyNchars()
    {
        //TODO: Unschön?
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

    /**
     * @throws Exception
     */
    public function generateUserPasswordReturnsValidPasswords()
    {
        for($i = 0; $i <= 1000000; $i++){
            $userPassword = $this->secretService->generateUserPassword(4);
            assertTrue($this->secretService->userPasswordIsValid($userPassword));
        }
    }
}
