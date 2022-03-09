<?php

namespace Hyqo\Wire\Part\Block;

use App\Chem\Attribute\PlainAttribute;
use Hyqo\Wire\Part\Attribute;
use Hyqo\Wire\Part\Directive;
use Hyqo\Wire\Touch;
use Hyqo\Wire\Utils;

class TagBlock extends Block
{
    use SiblingOperations;

    /** @var \DOMNode */
    protected $node;

    /** @var ?Wire */
    public $wire = null;

    /** @var Attribute[] */
    protected $attributes = [];

    /** @var Directive[] */
    protected $directives = [];

    /** @var Block[] */
    protected $children = [];

    /** @var ?TagBlock */
    protected $parent = null;

    /** @var string */
    protected $template;

    public function __construct(\DOMNode $node)
    {
        $this->node = $node;
        $this->wire = new Wire();

        if ($node instanceof \DOMText) {
            $this->template = $node->wholeText;
        } elseif (in_array($this->node->tagName, ['input', 'img'])) {
            $this->template = sprintf('<%1$s%%attributes%%/>', $this->node->tagName);
        } else {
            $this->template = sprintf(
                '<%1$s%%attributes%%>%%children%%</%1$s>',
                $this->node->tagName
            );
        }
    }

    public function getClosestStateful(): ?TagBlock
    {
        if ($this->wire->isStateful) {
            return $this;
        }

        $block = $this;

        while (null !== $parent = $block->getParent()) {
            if ($parent->wire->isStateful) {
                return $parent;
            }

            $block = $parent;
        }

        return null;
    }

    public function getParentStateful(): ?TagBlock
    {
        $block = $this;

        while (null !== $parent = $block->getParent()) {
            if ($parent->wire->isStateful) {
                return $parent;
            }

            $block = $parent;
        }

        return null;
    }

    public function getAttribute(string $name): ?Attribute
    {
        return $this->attributes[$name] ?? null;
    }

    public function setAttribute(string $name, ?string $value, ?int $order = null): Attribute
    {
        return $this->attributes[$name] = new Attribute($name, $value, $this, $order);
    }

    public function removeAttribute(string $name): void
    {
        unset($this->attributes[$name]);
    }

    public function getOrCreateAttribute(string $name, ?int $order = null): Attribute
    {
        $attribute = $this->getAttribute($name);

        if ($attribute) {
            return $attribute;
        }

        return $this->setAttribute($name, '', $order);
    }

    public function hasDirective(string $name): bool
    {
        return array_key_exists($name, $this->directives);
    }

    public function getDirective(string $name): ?Directive
    {
        return $this->directives[$name] ?? null;
    }

    public function setDirective(Directive $directive): Directive
    {
        return $this->directives[$directive->name] = $directive;
    }

    public function setDirectiveIfNotExists(Directive $directive): ?Directive
    {
        if ($this->hasDirective($directive->name)) {
            return null;
        }

        return $this->setDirective($directive);
    }

    public function renderAttributes(): string
    {
        if ($this->attributes) {
            usort($this->attributes, static function (Attribute $a, Attribute $b) {
                if ($a->order === $b->order) {
                    return 0;
                }

                return ($a->order < $b->order) ? -1 : 1;
            });

            return ' ' . implode(' ', $this->attributes);
        }

        return '';
    }

    public function isVirtual(): bool
    {
        return in_array($this->node->tagName, ['template', 'wrap']);
    }

    public function traverse(): void
    {
        foreach ($this->node->attributes as $nodeAttribute) {
            if ($nodeAttribute->name === 'onclick' || 0 === strpos($nodeAttribute->name, 'n:w-')) {
                $directive = Directive::from($nodeAttribute, $this);
                $directive->process();
                $this->directives[$directive->name] = $directive;
            } else {
                $attribute = Attribute::from($nodeAttribute, $this);
                $this->attributes[$attribute->name] = $attribute;
            }
        }

        $initialTouches = [];
        foreach ($this->directives as $directive) {
            foreach ($directive->getInitialBlockTouches() as $touch) {
                $initialTouches[] = $touch;
            }
        }

        $this->makeTouch($initialTouches);

        $touches = [];
        foreach ($this->directives as $directive) {
            foreach ($directive->getBlockTouches() as $touch) {
                $touches[] = $touch;
            }
        }

        $this->makeTouch($touches);
    }

    public function makeTouch(array $touches): void
    {
        usort($touches, static function (Touch $a, Touch $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }

            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });

        foreach ($touches as $touch) {
            $touch->make($this);
        }
    }

    public function addChild(Block $block): void
    {
        $this->children[] = $block;

        $block->setParent($this);
        $block->setScope($this->getScope());
        $block->order = count($this->children);

        if ($this->isVirtual()) {
            $block->level = $this->level;
        } else {
            $block->level = $this->level + 1;
        }
    }

    public function clean(): void
    {
        $this->children = [];
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

    /** @return TagBlock[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    protected function renderChildren(): string
    {
        if (!$this->children) {
            return '';
        }

        usort($this->children, static function (Block $a, Block $b) {
            if ($a->getOrder() === $b->getOrder()) {
                return 0;
            }

            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });


        $tree = [];

        foreach ($this->children as $i => $block) {
            if ($block instanceof TextBlock) {
//                if ('' === $text = trim($block)) {
//                    continue;
//                }

                $text = $block;

                $lastItem = end($tree);

                if (!$lastItem || !is_array($lastItem)) {
                    $tree[] = [];
                    end($tree);
                }

                $tree[key($tree)][] = $text;
            } else {
                $tree[] = (string)$block;
            }
        }

//        var_dump($tree);
        $string = implode(
            '',
            array_map(static function ($item) {
                if (is_array($item)) {
                    return implode(' ', $item);
                }

                return $item;
            }, $tree)
        );

        return $string;
    }

    public function __toString(): string
    {
        if ($this->isVirtual()) {
            $string = trim($this->renderChildren(), "\n");
            $spaces = [];
            foreach (explode("\n", $string) as $line) {
                if (preg_match('/^( *)/', $line, $matches)) {
                    $spaces[] = strlen($matches[1]);
                }
            }

            if ($spaces) {
                $string = preg_replace(sprintf('/^ {%d}/m', min($spaces)), '', $string);
            }

            return $string;
        }

        if ($this->wire->isLive) {
            $this->setAttribute('n:wire-var', $this->wire->id, 0);
            $this->getOrCreateAttribute('n:wire-init')->order = 10;
            $this->setAttribute('n:wire-pack', $this->wire->id, 999);
        }

//        $this->setAttribute('n:wire', Utils::pack(serialize($this->wire)), 1);

        if ($this->wire->statedId) {
//            $this->setAttribute('n:wire-state-var', $this->wire->statedId, 1);
        }

        if ($this->wire->isStateful) {
            $this->setAttribute('data-wire-stateful', null, 999);
//            $this->setAttribute('n:wire-state-var', $this->wire->statedId, 1);
        }

//        if ($wrappedWire = (string)$this->wire) {
//            $this->setAttribute('data-wire', $wrappedWire);
//        }

        return str_replace([
            '%attributes%',
            '%children%',
        ], [
            $this->renderAttributes(),
            $this->renderChildren(),
        ], $this->template);
    }
}
