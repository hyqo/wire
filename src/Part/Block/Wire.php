<?php

namespace Hyqo\Wire\Part\Block;

use Hyqo\Wire\Compiler;
use Hyqo\Wire\Part\Variable\Variable;

use function Hyqo\UUID\uid;

class Wire
{
    protected $parameters = [];

    public $id = null;

    public $statedId = null;

    /** @var bool */
    public $isLive = false;

    /** @var bool */
    public $isStateful = false;

    public function __construct()
    {
        $this->id = '$wire_' . uid(32);
    }

    public function setParameter(string $name, string $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function setBehavior(string $value): void
    {
        $this->isLive = true;
        Compiler::$requiredBehaviors[$value] = true;
    }

    public function setModel(string $value): void
    {
        $this->isLive = true;
        Compiler::$requiredModels[$value] = true;
    }
}
