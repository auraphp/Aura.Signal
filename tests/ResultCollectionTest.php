<?php
namespace Aura\Signal;

/**
 * Test class for ResultCollection.
 * Generated by PHPUnit on 2011-02-23 at 20:23:06.
 */
class ResultCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultCollection
     */
    protected $collection;
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

    public function newCollection($stopped = false)
    {
        $collection = new ResultCollection;
        
        $origin = new \StdClass;
        $sender = get_class($origin);
        $signal = 'mock_signal';
        
        for ($i = 0; $i < 5; $i++) {
            $value  = 'mock_value_' . $i;
            $result = $this->factory->newInstance(array(
                'origin'  => $origin,
                'sender'  => $sender,
                'signal'  => $signal,
                'value'   => $value,
            ));
            $collection->append($result);
        }
        
        if ($stopped) {
            $result = $this->factory->newInstance(array(
                'origin'  => $origin,
                'sender'  => $sender,
                'signal'  => $signal,
                'value'   => Manager::STOP,
            ));
            $collection->append($result);
        }
        return $collection;
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    public function testGetLast()
    {
        $collection = $this->newCollection();
        $result = $collection->getLast();
        $this->assertSame('mock_value_4', $result->value);
        
        $collection = new ResultCollection;
        $this->assertNull($collection->getLast());
    }
    
    public function testIsStopped()
    {
        $collection = $this->newCollection();
        $this->assertFalse($collection->isStopped());
        
        $collection = $this->newCollection(true);
        $this->assertTrue($collection->isStopped());
        
        $collection = new ResultCollection;
        $this->assertNull($collection->isStopped());
    }
}
