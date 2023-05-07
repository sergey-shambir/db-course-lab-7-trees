<?php
declare(strict_types=1);

namespace App\TreeOfLife\Model;

class TreeOfLife extends TreeOfLifeNode
{
    private ?TreeOfLife $parent;
    /** @var TreeOfLife[] */
    private array $children;

    /**
     * @param int $id - ID узла дерева жизни
     * @param string $name - название узла дерева жизни
     * @param bool $extinct - признак вымершего вида
     * @param int $confidence - степень уверенности в правильном местоположении вида (или иного узла) в заданном месте в дереве жизни
     * @param TreeOfLife|null $parent
     * @param TreeOfLife[] $children
     */
    public function __construct(int $id, string $name, bool $extinct, int $confidence, ?TreeOfLife $parent = null, array $children = [])
    {
        parent::__construct($id, $name, $extinct, $confidence);
        $this->parent = $parent;
        $this->children = $children;
    }

    public function getParent(): ?TreeOfLife
    {
        return $this->parent;
    }

    public function getChild(int $index): TreeOfLife
    {
        $child = $this->children[$index] ?? null;
        if (!$child)
        {
            throw new \OutOfBoundsException("No child with index $index");
        }
        return $child;
    }

    /**
     * @return TreeOfLife[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Безопасное добавление дочернего узла в дерево.
     * Выполняет проверки целостности.
     *
     * @param TreeOfLife $child
     * @return void
     */
    public function addChild(TreeOfLife $child): void
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

        $this->addChildUnsafe($child);
    }

    /**
     * Небезопасное добавление дочернего узла в дерево.
     * Не выполняет никаких проверок целостности.
     *
     * @param TreeOfLife $child
     * @return void
     */
    public function addChildUnsafe(TreeOfLife $child): void
    {
        $this->children[] = $child;
        $child->parent = $this;
    }

    /**
     * Возвращает узлы дерева или поддерева в виде списка, полученного в результате обхода в глубину
     *
     * @return TreeOfLife[]
     */
    public function listNodes(): array
    {
        $nodes = [];
        $this->walk(function (TreeOfLife $node) use (&$nodes) {
            $nodes[] = $node;
        });
        return $nodes;
    }

    /**
     * Обходит узлы дерева или поддерева в сперва глубину, начиная с заданного узла.
     *
     * @param callable $callback
     * @return void
     */
    public function walk(callable $callback): void
    {
        $callback($this);
        foreach ($this->children as $child)
        {
            $child->walk($callback);
        }
    }
}
