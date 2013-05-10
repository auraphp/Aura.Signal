<?php
namespace Aura\Signal;

use Aura\Framework\Test\WiringAssertionsTrait;

class WiringTest extends \PHPUnit_Framework_TestCase
{
    use WiringAssertionsTrait;

    protected function setUp()
    {
        $this->loadDi();
    }

    public function testServices()
    {
        $this->assertGet('signal_manager', 'Aura\Signal\Manager');
    }

    public function testInstances()
    {
        $this->assertNewInstance('Aura\Signal\Manager');
    }
}
