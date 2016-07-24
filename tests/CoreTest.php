<?php

use Spsr\Core;

class CoreTest extends PHPUnit_Framework_TestCase {
    public $core;

    public function setUp() {
        $this->core = new Core(array('env' => 'test'));
    }

    public function testCreate() {
        $this->assertEquals(7600010711, $this->core->ICN);
        $this->assertNotEmpty($this->core->SID);
    }
}