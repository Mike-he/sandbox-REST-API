<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161114025316 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A1107F99FC72');
        $this->addSql('ALTER TABLE room_building CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A1107F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA77F99FC72');
        $this->addSql('ALTER TABLE event CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY FK_D43829F77F99FC72');
        $this->addSql('ALTER TABLE food CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT FK_D43829F77F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B7F99FC72');
        $this->addSql('ALTER TABLE room CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B7F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');

        $this->addSql('DROP INDEX key_UNIQUE ON room_city');
        $this->addSql('ALTER TABLE room_city ADD parentId INT DEFAULT NULL, ADD level INT NOT NULL, DROP `key`');
        $this->addSql('ALTER TABLE room_city ADD CONSTRAINT FK_4E5FE85010EE4CEE FOREIGN KEY (parentId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4E5FE85010EE4CEE ON room_city (parentId)');
        $this->addSql('DELETE FROM room_city WHERE id > 0');
        $this->addSql("INSERT INTO `room_city`(`id`,`name`,`parentId`,`level`) VALUES(1,'中国',NULL,1),(2,'美国',NULL,1),(3,'北京',1,2),(4,'北京市',3,3),(5,'东城区',3,4),(6,'西城区',3,4),(7,'朝阳区',3,4),(8,'丰台区',3,4),(9,'石景山区',3,4),(10,'海淀区',3,4),(11,'门头沟区',3,4),(12,'房山区',3,4),(13,'通州区',3,4),(14,'顺义区',3,4),(15,'昌平区',3,4),(16,'大兴区',3,4),(17,'怀柔区',3,4),(18,'平谷区',3,4),(19,'密云区',3,4),(20,'延庆区',3,4),(21,'上海',1,2),(22,'上海市',21,3),(23,'黄浦区',21,4),(24,'徐汇区',21,4),(25,'长宁区',21,4),(26,'静安区',21,4),(27,'普陀区',21,4),(28,'闸北区',21,4),(29,'虹口区',21,4),(30,'杨浦区',21,4),(31,'闵行区',21,4),(32,'宝山区',21,4),(33,'嘉定区',21,4),(34,'浦东新区',21,4),(35,'金山区',21,4),(36,'松江区',21,4),(37,'青浦区',21,4),(38,'奉贤区',21,4),(39,'崇明县',21,4),(40,'广东省',1,2),(41,'广州市',40,3),(42,'荔湾区',41,4),(43,'越秀区',41,4),(44,'海珠区',41,4),(45,'天河区',41,4),(46,'白云区',41,4),(47,'黄埔区',41,4),(48,'番禺区',41,4),(49,'花都区',41,4),(50,'南沙区',41,4),(51,'从化区',41,4),(52,'增城区',41,4),(53,'深圳市',40,3),(54,'罗湖区',53,4),(55,'福田区',53,4),(56,'南山区',53,4),(57,'宝安区',53,4),(58,'龙岗区',53,4),(59,'盐田区',53,4),(60,'福建省',1,2),(61,'厦门市',60,3),(62,'思明区',61,4),(63,'海沧区',61,4),(64,'湖里区',61,4),(65,'集美区',61,4),(66,'同安区',61,4),(67,'翔安区',61,4),(68,'莆田市',61,4),(69,'城厢区',61,4),(70,'涵江区',61,4),(71,'荔城区',61,4),(72,'秀屿区',61,4),(73,'仙游县',61,4),(74,'浙江省',1,2),(75,'杭州市',74,3),(76,'上城区',75,4),(77,'下城区',75,4),(78,'江干区',75,4),(79,'拱墅区',75,4),(80,'西湖区',75,4),(81,'滨江区',75,4),(82,'萧山区',75,4),(83,'余杭区',75,4),(84,'富阳区',75,4),(85,'桐庐县',75,4),(86,'淳安县',75,4),(87,'建德市',75,4),(88,'临安市',75,4),(89,'重庆',1,2),(90,'重庆市',89,3),(91,'万州区',90,4),(92,'涪陵区',90,4),(93,'渝中区',90,4),(94,'大渡口区',90,4),(95,'江北区',90,4),(96,'沙坪坝区',90,4),(97,'九龙坡区',90,4),(98,'南岸区',90,4),(99,'北碚区',90,4),(100,'綦江区',90,4),(101,'大足区',90,4),(102,'渝北区',90,4),(103,'巴南区',90,4),(104,'黔江区',90,4),(105,'长寿区',90,4),(106,'江津区',90,4),(107,'合川区',90,4),(108,'永川区',90,4),(109,'南川区',90,4),(110,'璧山区',90,4),(111,'铜梁区',90,4),(112,'潼南区',90,4),(113,'荣昌区',90,4),(114,'梁平县',90,4),(115,'城口县',90,4),(116,'丰都县',90,4),(117,'垫江县',90,4),(118,'武隆县',90,4),(119,'忠县',90,4),(120,'开县',90,4),(121,'云阳县',90,4),(122,'奉节县',90,4),(123,'巫山县',90,4),(124,'巫溪县',90,4),(125,'石柱土家族自治县',90,4),(126,'秀山土家族苗族自治县',90,4),(127,'酉阳土家族苗族自治县',90,4),(128,'彭水苗族土家族自治县',90,4),(129,'山东省',1,2),(130,'青岛市',129,3),(131,'市南区',130,4),(132,'市北区',130,4),(133,'黄岛区',130,4),(134,'崂山区',130,4),(135,'李沧区',130,4),(136,'城阳区',130,4),(137,'胶州市',130,4),(138,'即墨市',130,4),(139,'平度市',130,4),(140,'莱西市',130,4),(141,'陕西省',1,2),(142,'西安市',141,3),(143,'新城区',142,4),(144,'碑林区',142,4),(145,'莲湖区',142,4),(146,'灞桥区',142,4),(147,'未央区',142,4),(148,'雁塔区',142,4),(149,'阎良区',142,4),(150,'临潼区',142,4),(151,'长安区',142,4),(152,'高陵区',142,4),(153,'蓝田县',142,4),(154,'周至县',142,4),(155,'户县',142,4),(156,'马萨诸塞州',2,3),(157,'波士顿',156,4),(158,'加利福尼亚州',2,3),(159,'旧金山',158,4)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
