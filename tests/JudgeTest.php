<?php

namespace Tests;

use PHPUnit_Framework_TestCase;

class JudgeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUriValid()
    {
        $judge = new \Judge\Judge('judge.coder.tips', 'test', 'test');
    }
}
