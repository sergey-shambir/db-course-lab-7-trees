DELIMITER //

CREATE PROCEDURE tree_of_life_nested_set_add_node(id INTEGER, parent_id INTEGER)
    COMMENT 'Вставляет узел под указанным родительским узлом правее всех уже существующих потомков. Вызывать процедуру надо внутри транзакции.'
    SQL SECURITY INVOKER
    MODIFIES SQL DATA
BEGIN
    DECLARE parent_lft INTEGER;
    DECLARE parent_rgt INTEGER;
    DECLARE parent_depth INTEGER;
    DECLARE error_text VARCHAR(128);

    -- Выбираем данные родительского узла
    SELECT
        lft,
        rgt,
        depth
    INTO parent_lft, parent_rgt, parent_depth
    FROM tree_of_life_nested_set
    WHERE
        node_id = parent_id;

    -- Если родительский узел не найден, завершаем процедуру с ошибкой
    IF parent_lft IS NULL THEN
        SET error_text = CONCAT('Parent node ', parent_id, 'not found');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_text;
    END IF;

    -- Сдвигаем левую границу интервала для всех узлов, которые находятся правее родителя.
    UPDATE tree_of_life_nested_set
    SET lft = lft + 2
    WHERE
        lft > parent_rgt;

    -- Сдвигаем правую границу интервала для всех предков и всех узлов, которые находятся правее родителя.
    UPDATE tree_of_life_nested_set
    SET rgt = rgt + 2
    WHERE
        rgt >= parent_rgt;

    -- Вставляем новый узел справа внутрь интервала родителя.
    INSERT INTO tree_of_life_nested_set (node_id, lft, rgt, depth)
    VALUES (id, parent_rgt, parent_rgt + 1, parent_depth + 1);
END;

//

CREATE PROCEDURE tree_of_life_nested_set_delete_sub_tree(id INTEGER)
    COMMENT 'Удаляет поддерево и выравнивает интервалы после удаления. Вызывать процедуру надо внутри транзакции.'
    SQL SECURITY INVOKER
    MODIFIES SQL DATA
BEGIN
    DECLARE node_lft INTEGER;
    DECLARE node_rgt INTEGER;
    DECLARE subtree_width INTEGER;

    -- Выбираем данные узла
    SELECT
        lft,
        rgt
    INTO node_lft, node_rgt
    FROM tree_of_life_nested_set
    WHERE
        node_id = id;

    -- Выполняем действия только если узел найден.
    IF node_lft IS NOT NULL THEN
        -- Удаляем поддерево, в том числе узлы поддерева
        DELETE tn
        FROM tree_of_life_node tn
            INNER JOIN tree_of_life_nested_set t ON tn.id = t.node_id
        WHERE
            t.lft >= node_lft AND
            t.rgt <= node_rgt;

        -- Вычисляем длину интервала удалённого поддерева
        SET subtree_width = node_rgt - node_lft + 1;

        -- Сдвигаем левую границу интервала для всех узлов, которые находились правее удалённого узла.
        UPDATE tree_of_life_nested_set
        SET lft = lft - subtree_width
        WHERE
            lft > node_rgt;

        -- Сдвигаем правую границу интервала для всех предков удалённого узла и всех узлов, которые находились
        --  правее удалённого узла.
        UPDATE tree_of_life_nested_set
        SET rgt = rgt - subtree_width
        WHERE
            rgt >= node_rgt;

    END IF;
END;

//

CREATE PROCEDURE tree_of_life_nested_set_delete_move_node(id INTEGER, new_parent_id INTEGER)
    COMMENT 'Перемещает узел к новому родителю и выравнивает интервалы. Вызывать процедуру надо внутри транзакции.'
    SQL SECURITY INVOKER
    MODIFIES SQL DATA
BEGIN
    DECLARE node_lft INTEGER;
    DECLARE node_rgt INTEGER;
    DECLARE node_depth INTEGER;
    DECLARE new_parent_lft INTEGER;
    DECLARE new_parent_rgt INTEGER;
    DECLARE new_parent_depth INTEGER;
    DECLARE subtree_width INTEGER;
    DECLARE move_offset INTEGER;
    DECLARE error_text VARCHAR(128);

    -- Выбираем данные перемещаемого узла
    SELECT
        lft,
        rgt,
        depth
    INTO node_lft, node_rgt, node_depth
    FROM tree_of_life_nested_set
    WHERE
        node_id = id;

    -- Если узел не найден, завершаем процедуру с ошибкой
    IF node_lft IS NULL THEN
        SET error_text = CONCAT('Node ', id, 'not found');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_text;
    END IF;

    -- Выбираем данные родительского узла
    SELECT
        lft,
        rgt,
        depth
    INTO new_parent_lft, new_parent_rgt, new_parent_depth
    FROM tree_of_life_nested_set
    WHERE
        node_id = new_parent_id;

    -- Если родительский узел не найден, завершаем процедуру с ошибкой
    IF new_parent_lft IS NULL THEN
        SET error_text = CONCAT('Parent node ', new_parent_id, 'not found');
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_text;
    END IF;

    -- При попытке сделать узел потомком своего потомка возвращаем ошибку
    IF node_lft >= new_parent_lft AND node_rgt <= new_parent_rgt THEN
        SET error_text = CONCAT('Cannot move node ', id, ' into descendant node ', new_parent_id);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_text;
    END IF;

    -- Вычисляем длину интервала, достаточного для поддерева
    SET subtree_width = node_rgt - node_lft + 1;

    -- Сдвигаем левую границу интервала для всех узлов, которые находятся правее родителя.
    UPDATE tree_of_life_nested_set
    SET lft = lft + subtree_width
    WHERE
        lft > new_parent_rgt;

    -- Сдвигаем правую границу интервала для всех предков и всех узлов, которые находятся правее родителя.
    UPDATE tree_of_life_nested_set
    SET rgt = rgt + subtree_width
    WHERE
        rgt >= new_parent_rgt;

    -- Если узел был сдвинут, меняем связанные переменные
    SET node_lft = IF(node_lft > new_parent_rgt, node_lft + subtree_width, node_lft);
    SET node_rgt = IF(node_rgt > new_parent_lft, node_rgt + subtree_width, node_rgt);

    -- Вычисляем смещение поддерева влево
    SET move_offset = new_parent_rgt - node_lft;

    -- Перемещаем поддерево на новое место справа внутри интервала родителя
    UPDATE tree_of_life_nested_set
    SET lft   = lft + move_offset,
        rgt   = rgt + move_offset,
        depth = depth - node_depth + new_parent_depth + 1
    WHERE
        lft >= node_lft AND
        rgt <= node_rgt;

    -- Устраняем пустой интервал, оставшийся после сдвига поддерева.
    UPDATE tree_of_life_nested_set
    SET lft = lft - subtree_width
    WHERE
        lft > node_lft;
    UPDATE tree_of_life_nested_set
    SET rgt = rgt - subtree_width
    WHERE
        rgt > node_rgt;
END;

//

DELIMITER ;
