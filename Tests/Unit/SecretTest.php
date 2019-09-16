<?php


use Hn\ShareASecret\Domain\Model\Secret;
use PHPUnit\Framework\TestCase;

class SecretTest extends TestCase
{
    private $message = 'message';
    private $indexHash = 'indexHash';

    /**
     * @test
     */
    public function secretWithoutMessageThrowsException()
    {
        $this->expectException(\TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException::class);
        new Secret('', 'asdf');
    }

    /**
     * @test
     */
    public function secretWithoutIndexHashThrowsException()
    {
        $this->expectException(\TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException::class);
        new Secret('asdf', '');
    }

    /**
     * @test
     */
    public function getMessage()
    {
        $secret = new Secret($this->message,$this->indexHash);
        $this->assertSame($this->message, $secret->getMessage());
    }

    /**
     * @test
     */
    public function getIndexHash()
    {
        $secret = new Secret($this->message,$this->indexHash);
        $this->assertSame($this->indexHash, $secret->getIndexHash());
    }
}
