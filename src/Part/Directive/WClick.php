<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WClick extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'onclick';
    public const PREFIX = 'onclick';

    protected $priority = 10;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $block->removeAttribute('onclick');
            $block->setAttribute('n:wire-click', $this->value);
        });
    }

}
