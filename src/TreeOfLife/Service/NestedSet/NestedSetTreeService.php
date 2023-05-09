<?php
declare(strict_types=1);

namespace App\TreeOfLife\Service\NestedSet;

use App\Common\Database\Connection;
use App\TreeOfLife\Data\TreeOfLifeNodeData;
use App\TreeOfLife\Database\TreeOfLifeServiceInterface;
use App\TreeOfLife\Model\TreeOfLifeNode;

class NestedSetTreeService implements TreeOfLifeServiceInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getNode(int $id): ?TreeOfLifeNodeData
    {
        // TODO: Implement getNode() method.
    }

    public function getTree(): TreeOfLifeNode
    {
        // TODO: Implement getTree() method.
    }

    public function getSubTree(int $id): TreeOfLifeNode
    {
        // TODO: Implement getSubTree() method.
    }

    public function getNodePath(int $id): array
    {
        // TODO: Implement getNodePath() method.
    }

    public function getParentNode(int $id): ?TreeOfLifeNodeData
    {
        // TODO: Implement getParentNode() method.
    }

    public function getChildren(int $id): array
    {
        // TODO: Implement getChildren() method.
    }

    public function saveTree(TreeOfLifeNode $root): void
    {
        // TODO: Implement saveTree() method.
    }

    public function addNode(TreeOfLifeNodeData $node, int $parentId): void
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