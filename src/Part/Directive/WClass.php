<?php

namespace Hyqo\Wire\Part\Directive;

use Hyqo\Wire\Part\Block\TagBlock;

use Hyqo\Wire\Utils;

use function Hyqo\String\s;

class WClass extends \Hyqo\Wire\Part\Directive
{
    public const NAME = 'w-class';
    public const PREFIX = 'w-class';

    protected $priority = 3;

    public function process(): void
    {
        $this->addBlockTouch(function (TagBlock $block) {
            $conditions = [];

            $mainClass = null;

            if ($classAttribute = $block->getAttribute('class')) {
                $currentClasses = s($classAttribute->value)->splitStrictly(' ');
            } else {
                $currentClasses = [];
            }

            array_walk($currentClasses, static function (&$class) use (&$mainClass) {
                if (!$mainClass) {
                    $mainClass = $class;
                }

                if (strpos($class, '!') === 0) {
                    $class = substr($class, 1);
                    $mainClass = $class;
                }
            });

            foreach (s($this->value)->splitStrictly(',') as $part) {
                if (!preg_match(
                    '/^(?P<negative>!|)?(?P<target>#[\w-]+|app|form|this|)\.(?P<state>[\w\-]+)\?(?P<class>[\w-]*)?$/',
                    $part,
                    $matches
                )) {
                    continue;
                }

                $comparison = $matches['negative'];
                $target = $matches['target'] ?: 'this';
                $state = $matches['state'];

                if (isset($matches['class'])) {
                    $class = $matches['class'];

                    if ($class && $mainClass && strpos($class, '--') === 0) {
                        $class = $mainClass . $class;
                    } elseif (!$class && $mainClass) {
                        $class = $mainClass . '--' . $state;
                    } elseif (!$class) {
                        $class = $state;
                    }

                    $condition = [$target, $state, $class, $comparison, null, false];

                    if ($target === 'form') {
                        if (null === $stateful = $block->getClosestStateful('form')) {
                            $condition[5] = true;
                        } else {
                            $condition[4] = $stateful->wire->id;
                        }
                    } elseif ($target === 'this') {
                        if (null === $stateful = $block->getClosestStateful()) {
                            $condition[5] = true;
                        } else {
                            $condition[4] = $stateful->wire->id;
                        }
                    } elseif ($target === 'parent') {
                        if (null === $stateful = $block->getParentStateful()) {
                            $condition[5] = true;
                        } else {
                            $condition[4] = $stateful->wire->id;
                        }
                    } else {
                        $condition[5] = true;
                    }

                    $conditions[] = $condition;
                }
            }

            if (!$conditions) {
                return;
            }

            $block->wire->isLive = true;

            $backendConditions = $currentClasses;

            foreach ($conditions as [$target, $state, $class, $comparison, $stateVar, $onFront]) {
                if ($onFront) {
                    continue;
                }

                if ($target === 'form' || $target === 'this' || $target === 'parent') {
                    $backendConditions[] = sprintf(
                        '(%2$s(%1$s[\'state\'][\'%3$s\'] ?? null) ? %4$s)',
                        $stateVar,
                        $comparison,
                        $state,
                        $class
                    );
                }
            }

            $frontendConditions =
                array_map(static function (array $condition) {
                    [$target, $state, $class, $comparison, , $onFront] = $condition;

                    $data = [$target, $state, $class];

                    if ($comparison) {
                        $data[] = $comparison;

                        if ($onFront) {
                            $data[] = true;
                        }
                    } elseif ($onFront) {
                        $data[] = '';
                        $data[] = true;
                    }

                    if ($class === $state) {
                        if (count($data) === 3) {
                            unset($data[2]);
                        } else {
                            $data[2] = '';
                        }
                    }

                    return $data;
                }, $conditions);

            if ($frontendConditions) {
                $block->setAttribute('n:wire-class-frontend', Utils::pack($frontendConditions), 4);
            }

            if ($backendConditions) {
                $block->removeAttribute('class');
                $nClassAttribute = $block->getOrCreateAttribute('n:class', 10);
                $nClassAttribute->order = 10;
                $nClassAttribute->value = implode(', ', $backendConditions);
            }
        });
    }

}
