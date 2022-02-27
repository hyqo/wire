<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class Fallback extends \Hyqo\Wire\Part\Directive
{
    protected $priority = 99;

    public function process(): void
    {
        $this->addInitialBlockTouch(function (TagBlock $block) {
            $block->wire->setParameter($this->name, $this->value);
        });
    }

}
