<?php

namespace Hyqo\Wire\Part\Block;

class TextBlock extends Block
{
    use SiblingOperations;

    /** @var ?TagBlock */
    protected $parent = null;

    /** @var string */
    protected $template;

    public function __construct(string $text)
    {
        $this->template = $text;
    }

    public function isVirtual(): bool
    {
        return false;
    }

    public function getParent(): ?TagBlock
    {
        return $this->parent;
    }

    public function setParent(TagBlock $parent): void
    {
        $this->parent = $parent;

        if ($this->scope === null) {
            $this->scope = $parent->scope;
        }
    }

    public function __toString(): string
    {
        return $this->template;
    }
}
