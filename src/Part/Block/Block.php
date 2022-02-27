<?php

namespace Hyqo\Wire\Part\Block;

use Hyqo\Wire\Scope;

abstract class Block implements BlockInterface
{
    /** @var ?Scope */
    protected $scope = null;

    /** @var int */
    protected $order = 0;

    /** @var int */
    protected $level = 0;

    public function getScope(): ?Scope
    {
        return $this->scope;
    }

    public function setScope(?Scope $scope): void
    {
        $this->scope = $scope;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getIndent(): string
    {
        return str_repeat(' ', $this->level * 4);
    }
}
