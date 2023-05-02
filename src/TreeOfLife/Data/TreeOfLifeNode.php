<?php
declare(strict_types=1);

namespace App\TreeOfLife\Data;

class TreeOfLifeNode
{
    private int $id;
    private string $name;
    private bool $extinct;
    private int $confidence;

    private ?TreeOfLifeNode $parent;
    /** @var TreeOfLifeNode[] */
    private array $children;

    public function __construct(int $id, string $name, bool $extinct, int $confidence, ?TreeOfLifeNode $parent = null, array $children = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->extinct = $extinct;
        $this->confidence = $confidence;
        $this->parent = $parent;
        $this->children = $children;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isExtinct(): bool
    {
        return $this->extinct;
    }

    public function getConfidence(): int
    {
        return $this->confidence;
    }

    public function getParent(): ?TreeOfLifeNode
    {
        return $this->parent;
    }

    /**
     * @return TreeOfLifeNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(TreeOfLifeNode $child): void
    {
        // Проверка на попытку установить множество родительских узлов.
        if ($child->parent)
        {
            if ($child->parent === $this)
            {
                return;
            }
            throw new \RuntimeException("Cannot add child node {$child->getId()}: this node already has parent");
        }
        // Проверка на циклические зависимости и на попытку установки узла родителем самого себя.
        for ($ancestor = $this; $ancestor !== null; $ancestor = $ancestor->parent)
        {
            if ($ancestor === $child)
            {
                throw new \RuntimeException("Cannot add node {$child->getId()} as child of {$this->getId()}: cyclic dependencies not allowed");
            }
        }

        $this->children[] = $child;
        $child->parent = $this;
    }
}
