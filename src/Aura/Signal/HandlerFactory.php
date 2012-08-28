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
 * A factory to create Handler objects.
 * 
 * @package Aura.Signal
 * 
 */
class HandlerFactory
{
    /**
     * 
     * An array of default parameters for Handler objects.
     * 
     * @var array
     * 
     */
    protected $params = [
        'sender'   => null,
        'signal'   => null,
        'callback' => null,
    ];

    /**
     * 
     * Creates and returns a new Handler object.
     * 
     * @param array $params An array of key-value pairs corresponding to
     * Handler constructor params.
     * 
     * @return Handler
     * 
     */
    public function newInstance(array $params)
    {
        $params = array_merge($this->params, $params);
        return new Handler(
            $params['sender'],
            $params['signal'],
            $params['callback']
        );
    }
}
