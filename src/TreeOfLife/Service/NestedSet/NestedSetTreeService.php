<?php
declare(strict_types=1);

namespace App\TreeOfLife\Service\NestedSet;

use App\Common\Database\Connection;
use App\TreeOfLife\Database\TreeOfLifeServiceInterface;
use App\TreeOfLife\Model\TreeOfLife;

class NestedSetTreeService implements TreeOfLifeServiceInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getTree(): TreeOfLife
    {
    }

    public function getSubTree(int $id): TreeOfLife
    {
        // TODO: Implement getSubTree() method.
    }

    public function getNodePath(int $id): array
    {
        // TODO: Implement getNodePath() method.
    }

    public function getParentNode(int $id): ?TreeOfLife
    {
        // TODO: Implement getParentNode() method.
    }

    public function getChildren(int $id): array
    {
        // TODO: Implement getChildren() method.
    }

    public function saveTree(TreeOfLife $root): void
    {
        // TODO: Implement saveTree() method.
    }

    public function addNode(TreeOfLife $node, int $parentId): void
    {
        // TODO: Implement addNode() method.
    }

    public function moveNode(int $id, int $newParentId): void
    {
        // TODO: Implement moveNode() method.
    }

    public function deleteSubTree(int $id): void
    {
        // TODO: Implement deleteSubTree() method.
    }
}