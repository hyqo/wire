<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WBehavior extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-behavior';
    public const PREFIX = 'w-behavior';

    protected $priority = 0;

    public function process(): void
    {
        $this->addInitialBlockTouch(function (TagBlock $block) {
            $block->wire->setBehavior($this->value);

            $block->setAttribute('n:wire-behavior', $this->value);

            if ($this->value === 'form') {
                if ($directive = $block->setDirectiveIfNotExists(new WState($block, ''))) {
                    $directive->process();
                }
            }

            if ($this->value === 'form.field') {
                if ($directive = $block->setDirectiveIfNotExists(new WState($block, ''))) {
                    $directive->process();
                }

                if ($directive = $block->setDirectiveIfNotExists(new WClass($block, '.invalid?'))) {
                    $directive->process();
                }
            }

            if ($this->value === 'form.button') {
                if ($directive = $block->setDirectiveIfNotExists(new WClass($block, '.running?'))) {
                    $directive->process();
                }
            }
        });
    }

}
