<?php
namespace aura\signal;
class Result
{
    protected $sender;
    
    protected $origin;
    
    protected $signal;
    
    protected $value;
    
    public function __construct($origin, $sender, $signal, $value)
    {
        $this->origin = $origin;
        $this->sender = $sender;
        $this->signal = $signal;
        $this->value  = $value;
    }
    
    public function __get($key)
    {
        return $this->$key;
    }
}
