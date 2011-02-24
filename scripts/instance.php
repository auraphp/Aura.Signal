<?php
require dirname(__DIR__) . "/src/Manager.php";
require dirname(__DIR__) . "/src/HandlerFactory.php";
require dirname(__DIR__) . "/src/ResultFactory.php";
require dirname(__DIR__) . "/src/ResultCollection.php";
use aura\signal\Manager;
use aura\signal\HandlerFactory;
use aura\signal\ResultFactory;
use aura\signal\ResultCollection;
return new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
