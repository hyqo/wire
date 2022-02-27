<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WState extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-state';
    public const PREFIX = 'w-state';

    protected $priority = 2;

    public function process(): void
    {
        $this->block->wire->isStateful = true;

        $this->addBlockTouch(function (TagBlock $block) {
            $block->setAttribute('n:wire-state', $this->value, 20);
        });
    }
}
