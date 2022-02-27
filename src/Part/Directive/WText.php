<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;
use Hyqo\Wire\Part\Block\TextBlock;

use Hyqo\Wire\Utils;

use function Hyqo\String\s;

class WText extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-text';
    public const PREFIX = 'w-text';

    protected $priority = 10;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $block->clean();

            $backendValue = '';
            $frontendValue = [];

            if (preg_match('/^(?P<target>#[\w-]+|app|this|parent|)\.(?P<state>[\w]+)$/', $this->value, $matches)) {
                $canProcessOnBackend = true;

                $target = $matches['target'] ?: 'this';
                $state = $matches['state'];

                $var = [$target, $state, null];

                if ($target === 'this') {
                    if (null === $stateful = $block->getClosestStateful()) {
                        $canProcessOnBackend = false;
                    } else {
                        $var[2] = $stateful->wire->id;
                    }
                } elseif ($target === 'parent') {
                    if (null === $stateful = $block->getparentStateful()) {
                        $canProcessOnBackend = false;
                    } else {
                        $var[2] = $stateful->wire->id;
                    }
                } else {
                    $canProcessOnBackend = false;
                }

                $frontendValue = [$target, $state];

                if ($canProcessOnBackend) {
                    $backendValue = sprintf(
                        '{%1$s[\'state\'][\'%2$s\'] ?? \'\'}',
                        $var[2],
                        $var[1]
                    );
                } else {
                    $frontendValue [] = true;
                }
            }

            $block->addChild(new TextBlock($backendValue));
            $block->setAttribute('n:wire-text-frontend', Utils::pack($frontendValue), 5);
        });
    }

}
