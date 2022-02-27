<?php

namespace Hyqo\Wire;

use Hyqo\Wire\Part\Block\TagBlock;

class Touch
{
    /** @var int */
    private $priority;

    private $closure;

    public function __construct(int $priority, callable $closure)
    {
        $this->priority = $priority;
        $this->closure = $closure;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function make(TagBlock $block): void
    {
        ($this->closure)($block);
    }
}
