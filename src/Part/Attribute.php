<?php

namespace Hyqo\Wire\Part;

use Hyqo\Wire\Part\Block\TagBlock;

class Attribute
{
    /** @var TagBlock */
    private $block;

    /** @var string */
    public $name;

    /** @var ?string */
    public $value;

    public $order;

    public function __construct(string $name, ?string $value, TagBlock $block, ?int $order = null)
    {
        $this->block = $block;
        $this->name = $name;
        $this->value = $value;
        $this->order = $order ?? 100;
    }

    public static function from(object $nodeAttribute, TagBlock $block): Attribute
    {
        return new Attribute($nodeAttribute->name, $nodeAttribute->value, $block);
    }

    public function __toString()
    {
        if ($this->value === null) {
            return $this->name;
        }

        return sprintf('%s="%s"', $this->name, is_string($this->value) ? $this->value : json_encode($this->value));
    }

    public function getBlock(): TagBlock
    {
        return $this->block;
    }
}
