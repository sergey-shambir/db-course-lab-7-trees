<?php
declare(strict_types=1);

namespace App\TreeOfLife\Service\NestedSet;

class NestedSetData
{
    private int $id;
    private int $left;
    private int $right;
    private int $depth;

    public function __construct(int $id, int $left, int $right, int $depth)
    {
        $this->id = $id;
        $this->left = $left;
        $this->right = $right;
        $this->depth = $depth;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLeft(): int
    {
        return $this->left;
    }

    public function getRight(): int
    {
        return $this->right;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }
}
