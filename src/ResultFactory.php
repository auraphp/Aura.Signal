<?php
namespace aura\signal;
class ResultFactory
{
    protected $base = array(
        'origin'  => null,
        'sender'  => null,
        'signal'  => null,
        'value'   => null,
    );
    
    public function newInstance(array $params)
    {
        $params = array_merge($this->base, $params);
        return new Result(
            $params['origin'],
            $params['sender'],
            $params['signal'],
            $params['value']
        );
    }
}
