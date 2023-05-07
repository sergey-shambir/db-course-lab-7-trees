<?php
declare(strict_types=1);

namespace Tests\App\TreeOfLife\AdjacencyList;

use App\TreeOfLife\IO\TreeOfLifeLoader;
use App\TreeOfLife\Model\TreeOfLife;
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

    public function testSaveTree(): void
    {
        $root = $this->loadTreeOfLife();
        $this->service->saveTree($root);
        $root2 = $this->service->getTree();
        $this->assertEqualTrees($root, $root2);
    }

    private function assertEqualTrees(TreeOfLife $expectedRoot, TreeOfLife $root): void
    {
        $this->assertEquals($expectedRoot->getId(), $root->getId());
        $this->assertEquals($expectedRoot->getName(), $root->getName());
        $this->assertEquals($expectedRoot->isExtinct(), $root->isExtinct());
        $this->assertEquals($expectedRoot->getConfidence(), $root->getConfidence());
        if ($expectedRoot->getParent())
        {
            $this->assertEquals($expectedRoot->getParent()->getId(), $root->getParent()->getId());
        }

        $expectedChildren = $expectedRoot->getChildren();
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
