<?php
namespace aura\signal;
class ResultCollection extends \ArrayObject
{
    public function getLast()
    {
        $k = count($this);
        if ($k > 0) {
            return $this[$k - 1];
        }
    }
    
    public function isStopped()
    {
        $last = $this->getLast();
        if ($last) {
            return $last->value === Manager::STOP;
        }
    }
}
