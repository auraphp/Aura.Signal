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
 * Represents a collection of Result objects.
 * 
 * @package Aura.Signal
 * 
 */
class ResultCollection extends \ArrayObject
{
    /**
     * 
     * override to avoid problems with Forge::newInstance() throwing
     * Fatal error: Uncaught exception 'InvalidArgumentException'
     * with message 'Passed variable is not an array or object, using 
     * empty array instead' in 
     * ~/system/package/Aura.Di/src/Aura/Di/Forge.php on line 103
     * 
     */
    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * 
     * Returns the last Result in the collection.
     * 
     * @return Result
     * 
     */
    public function getLast()
    {
        $k = count($this);
        if ($k > 0) {
            return $this[$k - 1];
        }
    }

    /**
     * 
     * Tells if the ResultCollection was stopped during processing.
     * 
     * @return bool
     * 
     */
    public function isStopped()
    {
        $last = $this->getLast();
        if ($last) {
            return $last->value === Manager::STOP;
        }
    }
}
