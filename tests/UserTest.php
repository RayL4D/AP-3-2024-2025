<?php

namespace App\Tests;

use App\Entity\User;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetPassword()
    {
        $userMock = $this->createMock(User::class);

        $userMock->method('getPassword')
                 ->willReturn('toto123');

        $this->assertEquals('toto123', $userMock->getPassword());
    }
}