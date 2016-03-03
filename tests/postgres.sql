-- Converted by db_converter
START TRANSACTION;
SET standard_conforming_strings=off;
SET escape_string_warning=off;
SET CONSTRAINTS ALL DEFERRED;

CREATE TABLE "application" (
    "id" integer NOT NULL,
    "author_id" integer NOT NULL,
    "maintainer_id" integer DEFAULT NULL,
    "title" varchar(100) NOT NULL,
    "web" varchar(200) DEFAULT NULL,
    "slogan" varchar(200) NOT NULL,
    PRIMARY KEY ("id")
);

INSERT INTO "application" VALUES (1,11,11,'Adminer','http://www.adminer.org/','Database management in single PHP file'),(2,11,NULL,'JUSH','http://jush.sourceforge.net/','JavaScript Syntax Highlighter'),(3,12,12,'Nette','http://nettephp.com/','Nette Framework for PHP 5'),(4,12,12,'Dibi','http://dibiphp.com/','Database Abstraction Library for PHP 5');
CREATE TABLE "application_tag" (
    "application_id" integer NOT NULL,
    "tag_id" integer NOT NULL,
    PRIMARY KEY ("application_id","tag_id")
);

INSERT INTO "application_tag" VALUES (1,21),(3,21),(4,21),(1,22),(4,22),(2,23);
CREATE TABLE "author" (
    "id" integer NOT NULL,
    "name" varchar(60) NOT NULL,
    "web" varchar(200) NOT NULL,
    "born" date DEFAULT NULL,
    PRIMARY KEY ("id")
);

INSERT INTO "author" VALUES (11,'Jakub Vrana','http://www.vrana.cz/',NULL),(12,'David Grudl','http://davidgrudl.com/',NULL);
CREATE TABLE "tag" (
    "id" integer NOT NULL,
    "name" varchar(40) NOT NULL,
    PRIMARY KEY ("id")
);

INSERT INTO "tag" VALUES (21,'PHP'),(22,'MySQL'),(23,'JavaScript');

-- Post-data save --
COMMIT;
START TRANSACTION;

-- Typecasts --

-- Foreign keys --
ALTER TABLE "application" ADD CONSTRAINT "application_author" FOREIGN KEY ("author_id") REFERENCES "author" ("id") DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "application" ("author_id");
ALTER TABLE "application" ADD CONSTRAINT "application_maintainer" FOREIGN KEY ("maintainer_id") REFERENCES "author" ("id") DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "application" ("maintainer_id");
ALTER TABLE "application_tag" ADD CONSTRAINT "application_tag_application" FOREIGN KEY ("application_id") REFERENCES "application" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "application_tag" ("application_id");
ALTER TABLE "application_tag" ADD CONSTRAINT "application_tag_tag" FOREIGN KEY ("tag_id") REFERENCES "tag" ("id") DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "application_tag" ("tag_id");

-- Sequences --
CREATE SEQUENCE application_id_seq;
SELECT setval('application_id_seq', max(id)) FROM application;
ALTER TABLE "application" ALTER COLUMN "id" SET DEFAULT nextval('application_id_seq');
CREATE SEQUENCE author_id_seq;
SELECT setval('author_id_seq', max(id)) FROM author;
ALTER TABLE "author" ALTER COLUMN "id" SET DEFAULT nextval('author_id_seq');
CREATE SEQUENCE tag_id_seq;
SELECT setval('tag_id_seq', max(id)) FROM tag;
ALTER TABLE "tag" ALTER COLUMN "id" SET DEFAULT nextval('tag_id_seq');

-- Full Text keys --

COMMIT;
