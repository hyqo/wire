<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

class WDecorate extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-decorate';
    public const PREFIX = 'w-decorate';

    protected $priority = 0;

    public function process(): void
    {
        $this->addInitialBlockTouch(function (TagBlock $block) {
            if ($this->value === 'form.field') {
                if ($directive = $block->setDirectiveIfNotExists(new WState($block, ''))) {
                    $directive->process();
                }

                if ($directive = $block->setDirectiveIfNotExists(new WClass($block, '.invalid?'))) {
                    $directive->process();
                }
            }

            if ($this->value === 'form.button') {
                if ($directive = $block->setDirectiveIfNotExists(new WState($block, ''))) {
                    $directive->process();
                }

                if ($directive = $block->setDirectiveIfNotExists(new WClass($block, 'form.running?'))) {
                    $directive->process();
                }
            }
        });
    }

}
