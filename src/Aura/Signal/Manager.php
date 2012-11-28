<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Signal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Signal;

/**
 * 
 * Processes signals through to Handler objects.
 * 
 * @package Aura.Signal
 * 
 */
class Manager
{
    /**
     * 
     * Indicates that the signal should not call more Handler instances.
     * 
     * @const string
     * 
     */
    const STOP = 'Aura\Signal\Manager::STOP';

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
    protected $handlers = [];

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
     * A ResultCollection from the last signal sent.
     * 
     * @var ResultCollection
     * 
     */
    protected $results;

    /**
     * 
     * Have the handlers for a signal been sorted by position?
     * 
     * @var array
     * 
     */
    protected $sorted = [];

    /**
     * 
     * Constructor.
     * 
     * @param HandlerFactory $handler_factory A factory to create Handler 
     * objects.
     * 
     * @param ResultFactory $result_factory A factory to create Result objects.
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
        array            $handlers = []
    ) {
        $this->handler_factory   = $handler_factory;
        $this->result_factory    = $result_factory;
        $this->result_collection = $result_collection;
        foreach ($handlers as $handler) {
            list($sender, $signal, $callback) = $handler;
            if (isset($handler[3])) {
                $position = $handler[3];
            } else {
                $position = 5000;
            }
            $this->handler($sender, $signal, $callback, $position);
        }
        $this->results = clone $this->result_collection;
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
        $handler = $this->handler_factory->newInstance([
            'sender'   => $sender,
            'signal'   => $signal,
            'callback' => $callback
        ]);
        $this->handlers[$signal][(int) $position][] = $handler;
        $this->sorted[$signal] = false;
    }

    /**
     * 
     * Gets Handler instances for the Manager.
     * 
     * @param string $signal Only get Handler instances for this signal; if 
     * null, get all Handler instances.
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
     * Invokes the Handler objects for a sender and signal.
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
        // clone a new result collection
        $this->results = clone $this->result_collection;

        // get the arguments to be passed to the handler
        $args = func_get_args();
        array_shift($args);
        array_shift($args);

        // now process the signal through the handlers and return the results
        $this->process($origin, $signal, $args);
        return $this->results;
    }

    /**
     * 
     * Invokes the Handler objects for a sender and signal.
     * 
     * @param object $origin The object sending the signal. Note that this is
     * always an object, not a class name.
     * 
     * @param string $signal The name of the signal from that origin.
     * 
     * @param $args Arguments to pass to the Handler callback.
     * 
     */
    protected function process($origin, $signal, $args)
    {
        // are there any handlers for this signal, regardless of sender?
        $list = $this->getHandlers($signal);
        if (! $list) {
            return;
        }

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
                    // prevents infinite looping). use process() instead
                    // of send() to prevent resetting the $results prop.
                    if ($origin !== $this) {
                        $this->process($this, 'handler_result', [$result]);
                    }

                    // retain the result
                    $this->results->append($result);

                    // should we stop processing?
                    if ($result->value === static::STOP) {
                        // yes, leave the processing loop
                        return;
                    }
                }
            }
        }
    }

    /**
     * 
     * Returns the ResultCollection from the last signal processing.
     * 
     * @return ResultCollection
     * 
     */
    public function getResults()
    {
        return $this->results;
    }
}
