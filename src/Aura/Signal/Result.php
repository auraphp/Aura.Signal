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
 * Represents a Result from a Handler.
 * 
 * @package Aura.Signal
 * 
 */
class Result
{
    /**
     * 
     * The origin object that actually sent the signal.
     * 
     * @var object
     * 
     */
    protected $origin;

    /**
     * 
     * The sender defined by the Handler.
     * 
     * @var mixed
     * 
     */
    protected $sender;

    /**
     * 
     * The signal defined by the Handler and sent by the origin.
     * 
     * @var mixed
     * 
     */
    protected $signal;

    /**
     * 
     * The value returned by the Handler callback, if any.
     * 
     * @var mixed
     * 
     */
    protected $value;

    /**
     * 
     * Constructor.
     * 
     * @param object $origin The origin object that sent the signal.
     * 
     * @param mixed $sender The sender as defined by the Handler.
     * 
     * @param string $signal The signal defined by the Handler and sent by 
     * the origin.
     * 
     * @param mixed $value The value returned by the Handler callback.
     * 
     */
    public function __construct($origin, $sender, $signal, $value)
    {
        $this->origin = $origin;
        $this->sender = $sender;
        $this->signal = $signal;
        $this->value  = $value;
    }

    /**
     * 
     * Make the properties available as magic read-only.
     * 
     * @param string $key The property name.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        return $this->$key;
    }
}
