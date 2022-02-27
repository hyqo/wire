<?php

namespace Hyqo\Wire\Part\Block;

trait SiblingOperations
{
    /** @param TagBlock|TextBlock $block */
    public function addSiblingBefore($block): void
    {
        if ($this->getParent() === null) {
            return;
        }

        $this->addChild($block);

        $block->order = $this->order;
        $block->level = $this->level + 1;
        $this->order++;
    }
}
