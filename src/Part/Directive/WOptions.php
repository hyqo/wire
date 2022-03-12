<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WOptions extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-options';
    public const PREFIX = 'w-options';

    protected $priority = 0;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $block->getOrCreateAttribute('n:wire-init')->value = 'options: [' . $this->value . ']';
        });
    }

}
