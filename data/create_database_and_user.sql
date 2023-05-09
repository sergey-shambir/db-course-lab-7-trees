CREATE DATABASE tree_of_life;

CREATE USER 'tree-of-life-app'@'%' IDENTIFIED BY 'A0h3dIzdy8';

GRANT ALL ON tree_of_life.* TO 'tree-of-life-app'@'%';
