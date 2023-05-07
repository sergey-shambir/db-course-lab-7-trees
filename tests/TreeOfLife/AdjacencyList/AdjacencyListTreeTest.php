<?php
declare(strict_types=1);

namespace Tests\App\TreeOfLife\AdjacencyList;

use App\TreeOfLife\IO\TreeOfLifeLoader;
use App\TreeOfLife\Model\TreeOfLife;
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
        $root = $this->loadTreeOfLife();
        $this->service->saveTree($root);
        $root2 = $this->service->getTree();
        $this->assertEqualTrees($root, $root2);

        $subTree = $this->service->getSubTree(14695);
        $this->assertEqualNodes(new TreeOfLifeNode(14695, 'none', false, 0), $subTree);
        $this->assertEqualNodes(new TreeOfLifeNode(14696, 'Pallenopsis', false, 0), $subTree->getChild(0));
        $this->assertEqualNodes(new TreeOfLifeNode(14697, 'Callipallenidae', false, 0), $subTree->getChild(1));
    }

    private function assertEqualNodes(TreeOfLifeNode $expected, TreeOfLifeNode $node): void
    {
        $this->assertEquals($expected->getId(), $node->getId());
        $this->assertEquals($expected->getName(), $node->getName());
        $this->assertEquals($expected->isExtinct(), $node->isExtinct());
        $this->assertEquals($expected->getConfidence(), $node->getConfidence());
    }

    private function assertEqualTrees(TreeOfLife $expected, TreeOfLife $root): void
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

    private function loadTreeOfLife(): TreeOfLife
    {
        $loader = new TreeOfLifeLoader();
        $loader->loadNodesCsv(self::NODES_CSV_PATH);
        $loader->loadLinksCsv(self::LINKS_CSV_PATH);
        return $loader->getTreeRoot();
    }
}
