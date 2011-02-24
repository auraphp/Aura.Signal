<?php
namespace aura\signal;

/**
 * Test class for Result.
 * Generated by PHPUnit on 2011-02-23 at 20:22:59.
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Result
     */
    protected $factory;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory = new ResultFactory;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @todo Implement test__get().
     */
    public function testResult()
    {
        $origin = new \StdClass;
        $sender = get_class($origin);
        $signal = 'mock_signal';
        $value  = 'mock_value';
        
        $result = $this->factory->newInstance(array(
            'origin'  => $origin,
            'sender'  => $sender,
            'signal'  => $signal,
            'value'   => $value,
        ));
        
        $this->assertType('aura\signal\Result', $result);
        $this->assertSame($result->origin, $origin);
        $this->assertSame($result->sender, $sender);
        $this->assertSame($result->signal, $signal);
        $this->assertSame($result->value, $value);
    }
}
