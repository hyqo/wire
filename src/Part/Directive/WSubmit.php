<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WSubmit extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'onsubmit';
    public const PREFIX = 'onsubmit';

    protected $priority = 10;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $block->removeAttribute('onsubmit');
            $block->setAttribute('n:wire-submit', $this->value);
        });
    }
}
