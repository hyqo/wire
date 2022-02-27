<?php

namespace Hyqo\Wire\Part\Block;

use Hyqo\Wire\Scope;

interface BlockInterface
{
    public function getScope(): ?Scope;
    public function setScope(Scope $scope): void;

    public function getOrder(): int;
    public function setOrder(int $order): void;

    public function getLevel(): int;
    public function setLevel(int $level): void;

    public function getIndent(): string;

    public function isVirtual(): bool;
}
