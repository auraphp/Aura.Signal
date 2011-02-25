<?php
namespace aura\signal;

/**
 * 
 * This is a SignalSlot implementation. Each Handler are inheritance-aware; if
 * you add a Handler for a Parent class signal, and a class Child that 
 * extends Parent sends that signal signal, the Handler for the Parent will 
 * handle it. This allows you to have generic Handler instances for class 
 * families, and specific Handler instances for subclasses.
 * 
 * @package aura.signal
 * 
 */
class Manager
{
    /**
     * Indicates that the signal should not call more Handler instances.
     */
    const STOP = 'aura\signal\Manager::STOP';
    
    /**
     * 
     * A factory to create Handler objects.
     * 
     * @var HandlerFactory
     * 
     */
    protected $handler_factory;
    
    /**
     * 
     * An array of Handler instances that respond to class signals.
     * 
     * @var array 
     * 
     */
    protected $handlers = array();
    
    /**
     * 
     * A prototype ResultCollection; this will be cloned by `send()` to retain
     * the Result objects from Handler instances.
     * 
     * @var ResultCollection
     * 
     */
    protected $result_collection;
    
    /**
     * 
     * A factory to create Result objects.
     * 
     * @var ResultFactory
     * 
     */
    protected $result_factory;
    
    /**
     * 
     * Have the handlers for a signal been sorted by position?
     * 
     * @var array
     * 
     */
    protected $sorted = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param HandlerFactory $handler_factory A factory to create Handler 
     * objects.
     * 
     * @param ResultCollection $result_collection A prototype ResultCollection.
     * 
     * @param array $handlers An array describing Handler params.
     * 
     */
    public function __construct(
        HandlerFactory   $handler_factory,
        ResultFactory    $result_factory,
        ResultCollection $result_collection,
        array            $handlers = null
    ) {
        $this->handler_factory   = $handler_factory;
        $this->result_factory    = $result_factory;
        $this->result_collection = $result_collection;
        foreach ((array) $handlers as $handler) {
            list($sender, $signal, $callback) = $handler;
            if (isset($handler[3])) {
                $position = $handler[3];
            } else {
                $position = 5000;
            }
            $this->handler($sender, $signal, $callback, $position);
        }
    }
    
    /**
     * 
     * Adds a Handler to respond to a sender signal.
     * 
     * @param string|object $sender The class or object sender of the signal.
     * If a class, inheritance will be honored, and '*' will be interpreted
     * as "any class."
     * 
     * @param string $signal The name of the signal for that sender.
     * 
     * @param callback The callback to execute when the signal is received.
     * 
     * @param int $position The handler processing position; lower numbers are
     * processed first. Use this to force a handler to be used before or after
     * others.
     * 
     * @return void
     * 
     */
    public function handler($sender, $signal, $callback, $position = 5000)
    {
        $handler = $this->handler_factory->newInstance(array(
            'sender'   => $sender,
            'signal'   => $signal,
            'callback' => $callback
        ));
        $this->handlers[$signal][(int) $position][] = $handler;
        $this->sorted[$signal] = false;
    }
    
    /**
     * 
     * Gets all Handler instances for the Manager.
     * 
     * @return array
     * 
     */
    public function getHandlers($signal = null)
    {
        if (! $signal) {
            return $this->handlers;
        }
        
        if (! isset($this->handlers[$signal])) {
            return;
        }
        
        if (! $this->sorted[$signal]) {
            ksort($this->handlers[$signal]);
        }
        
        return $this->handlers[$signal];
    }
    
    /**
     * 
     * Invokes the Handler for a sender and signal.
     * 
     * @param object $origin The object sending the signal. Note that this is
     * always an object, not a class name.
     * 
     * @param string $signal The name of the signal from that origin.
     * 
     * @params Arguments to pass to the Handler callback.
     * 
     * @return ResultCollection The results from each of the Handler objects.
     * 
     */
    public function send($origin, $signal)
    {
        // are there any handlers for this signal, regardless of sender?
        $list = $this->getHandlers($signal);
        if (! $list) {
            return;
        }
        
        // get the arguments to be passed to the handler
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        
        // clone a new result collection
        $collection = clone $this->result_collection;
        
        // go through the handler positions for the signal
        foreach ($list as $position => $handlers) {
            // go through each handler in this position
            foreach ($handlers as $handler) {
                    
                // try the handler
                $params = $handler->exec($origin, $signal, $args);
                // if it executed, it returned the params for a Result object
                if ($params) {
                    // create a Result object
                    $result = $this->result_factory->newInstance($params);
                    // allow a meta-handler to examine the Result object,
                    // but only if it wasn't sent from the Manager (this
                    // prevents infinite looping)
                    if ($origin !== $this) {
                        $this->send($this, 'handler_result', $result);
                    }
                    // retain the result
                    $collection->append($result);
                    // should we stop processing?
                    if ($result->value === static::STOP) {
                        // yes, leave the processing loop
                        return $collection;
                    }
                }
            }
        }
        
        // done!
        return $collection;
    }
}
