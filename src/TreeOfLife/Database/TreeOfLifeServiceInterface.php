<?php
declare(strict_types=1);

namespace App\TreeOfLife\Database;

use App\TreeOfLife\Model\TreeOfLife;

interface TreeOfLifeServiceInterface
{
    /**
     * Возвращает всё дерево целиком
     *
     * @return TreeOfLife
     */
    public function getTree(): TreeOfLife;

    /**
     * Возвращает ветвь дерева для указанного узла (т.е. поддерево данного узла)
     *
     * @param int $id
     * @return TreeOfLife
     */
    public function getSubTree(int $id): TreeOfLife;

    /**
     * Возвращает путь к узлу, т.е. последовательность всех его предков, начиная с корня
     *
     * @param int $id
     * @return TreeOfLife[]
     */
    public function getNodePath(int $id): array;

    /**
     * Возвращает родителя узла, т.е. его ближайшего предка
     *
     * @param int $id
     * @return TreeOfLife|null
     */
    public function getParentNode(int $id): ?TreeOfLife;

    /**
     * Возвращает список дочерних узлов к узлу, т.е ближайших потомков
     *
     * @param int $id
     * @return TreeOfLife[]
     */
    public function getChildren(int $id): array;

    /**
     * Сохраняет всё дерево целиком.
     *
     * @param TreeOfLife $root
     * @return void
     */
    public function saveTree(TreeOfLife $root): void;

    /**
     * Добавляет узел к дереву.
     * При попытке добавить узел, уже содержащий дочерние узлы, бросается InvalidArgumentException
     *
     * @param TreeOfLife $node
     * @param int $parentId
     * @return void
     * @throws \InvalidArgumentException
     */
    public function addNode(TreeOfLife $node, int $parentId): void;

    /**
     * Перемещает узел к новому родителю.
     * При попытке сделать узел дочерним для самого себя или своих потомков бросается InvalidArgumentException.
     *
     * @param int $id
     * @param int $newParentId
     * @return void
     * @throws \InvalidArgumentException
     */
    public function moveNode(int $id, int $newParentId): void;

    /**
     * Удаляет узел и всех его потомков.
     *
     * @param int $id
     * @return void
     * @throws \LogicException
     */
    public function deleteSubTree(int $id): void;
}
