<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

use Hyqo\Wire\Utils;

use function Hyqo\String\s;

class WVisible extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-visible';
    public const PREFIX = 'w-visible';

    protected $priority = 3;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $canProcessOnBackend = true;
            $conditions = [];

            $parts = s($this->value)->splitStrictly(',');

            foreach ($parts as $part) {
                if (!preg_match(
                    '/^(?P<negative>!|)?(?P<target>#[\w-]+|app|this|parent|)\.(?P<state>[\w]+)$/',
                    $part,
                    $matches
                )) {
                    continue;
                }

                $comparison = $matches['negative'];
                $target = $matches['target'] ?: 'this';
                $state = $matches['state'];

                $condition = [$target, $state, $comparison, null];

                if ($target === 'this') {
                    if (null === $stateful = $block->getClosestStateful()) {
                        $canProcessOnBackend = false;
                    } else {
                        $condition[3] = $stateful->wire->id;
                    }
                } elseif ($target === 'parent') {
                    if (null === $stateful = $block->getparentStateful()) {
                        $canProcessOnBackend = false;
                    } else {
                        $condition[3] = $stateful->wire->id;
                    }
                } else {
                    $canProcessOnBackend = false;
                }

                $conditions[] = $condition;
            }

            $backendConditions = [];

            if ($canProcessOnBackend) {
                foreach ($conditions as [$target, $state, $comparison, $stateVar]) {
                    if ($target === 'this' || $target === 'parent') {
                        $backendConditions[] = sprintf(
                            'true === %2$s(bool)(%1$s[\'state\'][\'%3$s\'] ?? null)',
                            $stateVar,
                            $comparison,
                            $state
                        );
                    }
                }

                if ($styleAttribute = $block->getAttribute('style')) {
                    $block->removeAttribute('style');
                    $block->setAttribute('n:wire-style', $styleAttribute->value);
                }
            }

            $frontendConditions = [
                array_map(static function (array $condition) {
                    [$target, $state, $comparison,] = $condition;

                    $data = [$target, $state];

                    if ($comparison) {
                        $data[] = $comparison;
                    }

                    return $data;
                }, $conditions)
            ];

            if (!$canProcessOnBackend) {
                $frontendConditions[] = true;
            }

            $block->wire->isLive = true;

            if ($backendConditions) {
                $block->setAttribute('n:wire-visible-backend', implode(', ', $backendConditions), 4);
            }

            if ($frontendConditions) {
                $block->setAttribute('n:wire-visible-frontend', Utils::pack($frontendConditions), 4);
            }
        });
    }

}
