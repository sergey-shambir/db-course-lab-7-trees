DELIMITER //

-- Удалить старую можно так:
-- DROP PROCEDURE tree_of_life_nested_set_add_node;

CREATE PROCEDURE tree_of_life_nested_set_add_node(id INTEGER, parent_id INTEGER)
    COMMENT 'Вставляет узел под указанным родительским узлом правее всех уже существующих потомков. Вызывать процедуру надо внутри транзакции.'
    SQL SECURITY INVOKER
    MODIFIES SQL DATA
BEGIN
    DECLARE parent_lft, parent_rgt, parent_depth INTEGER;
    DECLARE error_text VARCHAR(128);
    DECLARE need_transaction BOOLEAN;

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

DELIMITER ;
