<?php

namespace Hyqo\Wire\Part;

use Hyqo\Wire\Part\Block\TagBlock;
use Hyqo\Wire\Part\Directive\Fallback;
use Hyqo\Wire\Part\Directive\WBehavior;
use Hyqo\Wire\Part\Directive\WClass;
use Hyqo\Wire\Part\Directive\WClick;
use Hyqo\Wire\Part\Directive\WDecorate;
use Hyqo\Wire\Part\Directive\WModel;
use Hyqo\Wire\Part\Directive\WOptions;
use Hyqo\Wire\Part\Directive\WState;
use Hyqo\Wire\Part\Directive\WSubmit;
use Hyqo\Wire\Part\Directive\WText;
use Hyqo\Wire\Part\Directive\WVisible;
use Hyqo\Wire\Touch;

abstract class Directive
{
    public const NAME = '';
    public const PREFIX = '';
    public const PARAMS = [];

    protected const PARAMS_REGEX = '\.[\w]+(?:\([\d]+\))?)+';

    /** @var TagBlock */
    protected $block;

    /** @var Touch[] */
    protected $initialTouches = [];

    /** @var Touch[] */
    protected $touches = [];

    /** @var string */
    public $name;

    public $value;

    public function __construct(TagBlock $block, string $value, ?string $name = null)
    {
        $this->block = $block;
        $this->name = $name ?? static::NAME;
        $this->value = $value;

        $block->wire->isLive = true;
    }

    public static function from(object $nodeAttribute, TagBlock $block): Directive
    {
        $name = preg_replace('/^n:/', '', $nodeAttribute->name);

        foreach (
            [
                WDecorate::class,
                WBehavior::class,
                WModel::class,
                WOptions::class,
                WState::class,
                WClass::class,
                WVisible::class,
                WText::class,
            ] as $directiveClass
        ) {
            if (preg_match(call_user_func([$directiveClass, 'buildRegex']), $name)) {
                return new $directiveClass($block, $nodeAttribute->value);
            }
        }

        if ($nodeAttribute->name === 'onclick') {
            return new WClick($block, $nodeAttribute->value);
        }

        if ($nodeAttribute->name === 'onsubmit') {
            return new WSubmit($block, $nodeAttribute->value);
        }

        return new Fallback($block, $nodeAttribute->value, preg_replace('/^w-/', '', $nodeAttribute->name));
    }

    public static function buildRegex(): string
    {
        return sprintf('/^%s%s/', static::PREFIX, self::PARAMS ? '(?P<params>(?:' . self::PARAMS_REGEX . ')+)' : '');
    }

    public function process(): void
    {
    }

    public function addBlockTouch(callable $closure): void
    {
        $this->touches[] = new Touch($this->priority, $closure);
    }

    public function addInitialBlockTouch(callable $closure): void
    {
        $this->initialTouches[] = new Touch($this->priority, $closure);
    }

    /** @return Touch[] */
    public function getInitialBlockTouches(): array
    {
        return $this->initialTouches;
    }

    /** @return Touch[] */
    public function getBlockTouches(): array
    {
        return $this->touches;
    }
}
