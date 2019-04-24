<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit\Framework\TestCase;
use SilverStripe\MFA\State\Result;

class ResultTest extends TestCase
{
    public function testSetContext()
    {
        $result = new Result(false, 'Oops!', ['tired' => true]);
        $newResult = $result->setContext(['tired' => false]);
        $this->assertNotSame($result->getContext(), $newResult->getContext(), 'Result should be immutable');
    }

    public function testGetContext()
    {
        $result = new Result(true, 'Test', ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $result->getContext());
    }

    public function testSetMessage()
    {
        $result = new Result();
        $newResult = $result->setMessage('foo');
        $this->assertNotSame($result->getMessage(), $newResult->getMessage(), 'Result should be immutable');
    }

    public function testGetMessage()
    {
        $result = new Result(true, 'Hello');
        $this->assertSame('Hello', $result->getMessage());
    }

    public function testIsSuccessful()
    {
        $result = new Result(false);
        $this->assertFalse($result->isSuccessful());
    }

    public function testSetSuccess()
    {
        $result = new Result(false);
        $newResult = $result->setSuccessful(true);
        $this->assertNotSame($result->isSuccessful(), $newResult->isSuccessful(), 'Result should be immutable');
    }
}
