<?php
declare(strict_types=1);

namespace Tests\App\TreeOfLife\AdjacencyList;

use App\TreeOfLife\Data\TreeOfLifeNodeData;
use App\TreeOfLife\IO\TreeOfLifeLoader;
use App\TreeOfLife\Model\TreeOfLifeNode;
use App\TreeOfLife\Service\AdjacencyList\AdjacencyListTreeService;
use Tests\App\Common\AbstractDatabaseTestCase;

class AdjacencyListTreeTest extends AbstractDatabaseTestCase
{
    private const DATA_DIR = __DIR__ . '/../../../data';
    private const NODES_CSV_PATH = self::DATA_DIR . '/treeoflife_nodes.csv';
    private const LINKS_CSV_PATH = self::DATA_DIR . '/treeoflife_links.csv';

    private AdjacencyListTreeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->execute('DELETE FROM tree_of_life_adjacency_list');
        $this->getConnection()->execute('DELETE FROM tree_of_life_node');

        $this->service = new AdjacencyListTreeService($this->getConnection());
    }

    public function testSaveAndLoadTree(): void
    {
        // Arrange
        $root = $this->loadTreeOfLife();
        $this->service->saveTree($root);

        // Act
        $root2 = $this->service->getTree();
        // Assert
        $this->assertEqualTrees($root, $root2);

        // Act
        $subTree = $this->service->getSubTree(14695);
        // Assert
        $this->assertTreeNode(new TreeOfLifeNodeData(14695, 'none', false, 0), $subTree);
        $this->assertTreeNode(new TreeOfLifeNodeData(14696, 'Pallenopsis', false, 0), $subTree->getChild(0));
        $this->assertTreeNode(new TreeOfLifeNodeData(14697, 'Callipallenidae', false, 0), $subTree->getChild(1));
    }

    public function testGetNodePath(): void
    {
        // Arrange
        $root = $this->loadTreeOfLife();
        $this->service->saveTree($root);

        // Act
        $path = $this->service->getNodePath(14697);

        // Assert
        $this->assertCount(14, $path);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(14697, 'Callipallenidae', false, 0), $path[0]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(14695, 'none', false, 0), $path[1]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2539, 'Pycnogonida', false, 0), $path[2]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2535, 'Chelicerata', false, 0), $path[3]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2469, 'Arthropoda', false, 0), $path[4]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2468, 'none', false, 0), $path[5]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2467, 'Ecdysozoa', false, 0), $path[6]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2459, 'Bilateria', false, 0), $path[7]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2458, 'none', false, 0), $path[8]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2374, 'Animals', false, 0), $path[9]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2373, 'none', false, 0), $path[10]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(2372, 'Opisthokonts', false, 0), $path[11]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(3, 'Eukaryotes', false, 0), $path[12]);
        $this->assertTreeNodeData(new TreeOfLifeNodeData(1, 'Life on Earth', false, 0), $path[13]);

    }

    private function assertTreeNode(TreeOfLifeNodeData $expected, TreeOfLifeNode $node): void
    {
        $this->assertEquals($expected->getId(), $node->getId());
        $this->assertEquals($expected->getName(), $node->getName());
        $this->assertEquals($expected->isExtinct(), $node->isExtinct());
        $this->assertEquals($expected->getConfidence(), $node->getConfidence());
    }

    private function assertTreeNodeData(TreeOfLifeNodeData $expected, TreeOfLifeNodeData $node): void
    {
        $this->assertEquals($expected->getId(), $node->getId());
        $this->assertEquals($expected->getName(), $node->getName());
        $this->assertEquals($expected->isExtinct(), $node->isExtinct());
        $this->assertEquals($expected->getConfidence(), $node->getConfidence());
    }

    private function assertEqualTrees(TreeOfLifeNode $expected, TreeOfLifeNode $root): void
    {
        $this->assertEquals($expected->getId(), $root->getId());
        $this->assertEquals($expected->getName(), $root->getName());
        $this->assertEquals($expected->isExtinct(), $root->isExtinct());
        $this->assertEquals($expected->getConfidence(), $root->getConfidence());
        if ($expected->getParent())
        {
            $this->assertEquals($expected->getParent()->getId(), $root->getParent()->getId());
        }

        $expectedChildren = $expected->getChildren();
        $children = $root->getChildren();
        $this->assertCount(count($expectedChildren), $children);

        for ($i = 0, $iMax = count($expectedChildren); $i < $iMax; ++$i)
        {
            $this->assertEqualTrees($expectedChildren[$i], $children[$i]);
        }
    }

    private function loadTreeOfLife(): TreeOfLifeNode
    {
        $loader = new TreeOfLifeLoader();
        $loader->loadNodesCsv(self::NODES_CSV_PATH);
        $loader->loadLinksCsv(self::LINKS_CSV_PATH);
        return $loader->getTreeRoot();
    }
}
