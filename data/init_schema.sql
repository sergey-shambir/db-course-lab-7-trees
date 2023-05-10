USE tree_of_life;

CREATE TABLE tree_of_life_node
(
  id         INT          NOT NULL,
  name       VARCHAR(200) NOT NULL,
  extinct    BOOLEAN      NOT NULL,
  confidence INT          NOT NULL,
  PRIMARY KEY (id)
);

-- Хранит структуру дерева в виде Adjacency List
CREATE TABLE tree_of_life_adjacency_list
(
  node_id   INT NOT NULL,
  parent_id INT NOT NULL,
  PRIMARY KEY (node_id),
  CONSTRAINT tol_adjacency_list_node_id
    FOREIGN KEY (node_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE,
  CONSTRAINT tol_adjacency_list_parent_id
    FOREIGN KEY (parent_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE
);

-- Хранит структуру дерева в виде Nested Set
CREATE TABLE tree_of_life_nested_set
(
  node_id INT NOT NULL,
  lft     INT NOT NULL,
  rgt     INT NOT NULL,
  depth   INT NOT NULL,
  PRIMARY KEY (node_id),
  CONSTRAINT tol_nested_set_list_node_id
    FOREIGN KEY (node_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE,
  INDEX lft_rgt_idx (lft, rgt),
  INDEX depth_lft_rgt_idx (depth, lft, rgt)
);

-- Хранит структуру дерева в виде Closure Table
CREATE TABLE tree_of_life_closure_table
(
  node_id     INT NOT NULL,
  ancestor_id INT NOT NULL,
  distance    INT NOT NULL,
  PRIMARY KEY (node_id, ancestor_id),
  INDEX parent_id_distance_idx (ancestor_id, distance),
  CONSTRAINT tol_closure_table_node_id
    FOREIGN KEY (node_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE,
  CONSTRAINT tol_closure_table_parent_id
    FOREIGN KEY (ancestor_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE
);

-- Хранит структуру дерева в виде Materialized Path
CREATE TABLE tree_of_life_materialized_path
(
  node_id INT          NOT NULL,
  path    VARCHAR(200) NOT NULL,
  PRIMARY KEY (node_id),
  CONSTRAINT tol_materialized_path_list_node_id
    FOREIGN KEY (node_id) REFERENCES tree_of_life_node (id) ON DELETE CASCADE
);
