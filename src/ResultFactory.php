<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace aura\signal;

/**
 * 
 * A factory to create Result objects.
 * 
 * @package aura.signal
 * 
 */
class ResultFactory
{
    /**
     * 
     * An array of default parameters for Result objects.
     * 
     * @var array
     * 
     */
    protected $params = array(
        'origin'  => null,
        'sender'  => null,
        'signal'  => null,
        'value'   => null,
    );
    
    /**
     * 
     * Creates and returns a new Option object.
     * 
     * @param array $params An array of key-value pairs corresponding to
     * Result constructor params.
     * 
     * @return Result
     * 
     */
    public function newInstance(array $params)
    {
        $params = array_merge($this->params, $params);
        return new Result(
            $params['origin'],
            $params['sender'],
            $params['signal'],
            $params['value']
        );
    }
}
