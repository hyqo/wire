<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WModel extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-model';
    public const PREFIX = 'w-model';

    protected $priority = 0;

    public function process(): void
    {
        $this->addInitialBlockTouch(function (TagBlock $block) {
            $block->wire->setModel($this->value);

            $block->setAttribute('n:wire-model', $this->value);
        });
    }

}
