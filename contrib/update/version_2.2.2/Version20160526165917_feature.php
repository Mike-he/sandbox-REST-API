<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160526165917_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
            CREATE TABLE `UserPhoneCode` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `cnName` VARCHAR(255) NOT NULL,
              `enName` VARCHAR(255) NOT NULL,
              `code` VARCHAR(122) NOT NULL,
              PRIMARY KEY (`id`)
            )
        ");
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿富汗","Afghanistan","+93")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿拉斯加","Alaska","+1907")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿尔巴尼亚","Albania","+355")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿尔及利亚","Algeria","+213")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("美国","United States","+1")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("安道尔","Andorra","+376")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("安哥拉","Angola","+244")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("安圭拉岛","Anguilla I.","+1264")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("安提瓜和巴布达","Antigua and Barbuda","+1268")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿根廷","Argentina","+54")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("亚美尼亚","Armenia","+374")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿鲁巴岛","Aruba I.","+297")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿森松","Ascension","+247")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("澳大利亚","Australia","+61")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("奥地利","Austria","+43")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿塞拜疆","Azerbaijan","+994")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴林","Bahrain","+973")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("孟加拉国","Bangladesh","+880")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴巴多斯","Barbados","+1246")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("白俄罗斯","Belarus","+375")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("比利时","Belgium","+32")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("伯利兹","Belize","+501")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("贝宁","Benin","+229")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("百慕大群岛","Bermuda Is.","+1441")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("不丹","Bhutan","+975")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("玻利维亚","Bolivia","+591")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("波斯尼亚和黑塞哥维那","Bosnia And Herzegovina","+387")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("博茨瓦纳","Botswana","+267")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴西","Brazil","+55")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("保加利亚","Bulgaria","+359")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("布基纳法索","Burkinafaso","+226")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("布隆迪","Burundi","+257")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("喀麦隆","Cameroon","+237")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("加拿大","Canada","+1")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("加那利群岛","Canaries Is.","+34")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("佛得角","Cape Verde","+238")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("开曼群岛","Cayman Is.","+1345")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("中非","Central Africa","+236")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("乍得","Chad","+235")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("中华人民共和国","China","+86")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("智利","Chile","+56")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣诞岛","Christmas I.","+61 9164 ")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("科科斯岛","Cocos I.","+61 9162 ")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("哥伦比亚","Colombia","+57")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴哈马国","Commonwealth of The Bahamas","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("多米尼克国","Commonwealth of dominica","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("科摩罗","Comoro","+269")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("刚果","Congo","+242")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("科克群岛","Cook IS.","+682")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("哥斯达黎加","Costa Rica","+506")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("克罗地亚","Croatian","+383 385 ")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("古巴","Cuba","+53")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("塞浦路斯","Cyprus","+357")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("捷克","Czech","+420")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("丹麦","Denmark","+45")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("迪戈加西亚岛","Diego Garcia I.","+246")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("吉布提","Djibouti","+253")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("多米尼加共和国","Dominican Rep.","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("厄瓜多尔","Ecuador","+593")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("埃及","Egypt","+20")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("萨尔瓦多","El Salvador","+503")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("赤道几内亚","Equatorial Guinea","+240")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("厄立特里亚","Eritrea","+291")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("爱沙尼亚","Estonia","+372")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("埃塞俄比亚","Ethiopia","+251")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("福克兰群岛","Falkland Is.","+500")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("法罗群岛","Faroe Is.","+298")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("斐济","Fiji","+679")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("芬兰","Finland","+358")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("法国","France","+33")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("法属圭亚那","French Guiana","+594")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("法属波里尼西亚","French Polynesia","+689")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("加蓬","Gabon","+241")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("冈比亚","Gambia","+220")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("格鲁吉亚","Georgia","+995")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("德国","Germany","+49")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("加纳","Ghana","+233")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("直布罗陀","Gibraltar","+350")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("希腊","Greece","+30")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("格陵兰岛","Greenland","+299")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("格林纳达","Grenada","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瓜德罗普岛","Guadeloupe I.","+590")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("关岛","Guam","+671")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("危地马拉","Guatemala","+502")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("几内亚","Guinea","+224")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("几内亚比绍","Guinea-bissau","+245")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圭亚那","Guyana","+592")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("海地","Haiti","+509")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("夏威夷","Hawaii","+1808")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("洪都拉斯","Honduras","+504")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("匈牙利","HunGary","+36")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("冰岛","Iceland","+354")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("印度","India","+91")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("印度尼西亚","Indonesia","+62")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("伊郎","Iran","+98")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("伊拉克","Iraq","+964")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("爱尔兰","Ireland","+353")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("以色列","Israel","+972")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("意大利","Italy","+39")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("科特迪瓦","Ivory Coast","+225")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("牙买加","Jamaica","+1876")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("日本","Japan","+81")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("约旦","Jordan","+962")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("柬埔寨","Cambodia","+855")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("哈萨克斯坦","Kazakhstan","+7")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("肯尼亚","Kenya","+254")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("基里巴斯","Kiribati","+686")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("朝鲜","North Korea","+850")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("韩国","South Korea","+82")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("科威特","Kuwait","+965")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("吉尔吉斯斯坦","Kyrgyzstan","+7")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("老挝","Laos","+856")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("拉脱维亚","Latvia","+371")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("黎巴嫩","Lebanon","+961")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("莱索托","Lesotho","+266")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("利比里亚","Liberia","+231")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("利比亚","Libya","+218")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("列支敦士登","Liechtenstein","+41 75 ")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("立陶宛","Lithuania","+370")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("卢森堡","Luxembourg","+352")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马其顿","Macedonia","+389")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马达加斯加","Madagascar","+261")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马拉维","Malawi","+265")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马来西亚","Malaysia","+60")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马尔代夫","Maldive","+960")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马里","Mali","+223")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马耳他","Malta","+356")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马里亚纳群岛","Mariana Is.","+670")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马绍尔群岛","Marshall Is.","+692")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马提尼克","Martinique","+596")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("毛里塔尼亚","Mauritania","+222")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("毛里求斯","Mauritius","+230")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("马约特岛","Mayotte I.","+269")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("墨西哥","Mexico","+52")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("密克罗尼西亚","Micronesia","+691")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("中途岛","Midway I.","+1808")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("摩尔多瓦","Moldova","+373")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("摩纳哥","Monaco","+377")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("蒙古","Mongolia","+976")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("蒙特塞拉特岛","Montserrat I.","+1664")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("摩洛哥","Morocco","+212")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("莫桑比克","Mozambique","+258")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("缅甸","Myanmar","+95")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("纳米比亚","Namibia","+264")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瑙鲁","Nauru","+674")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("尼泊尔","Nepal","+977")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("荷兰","Netherlands","+31")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("荷属安的列斯群岛","Netherlandsantilles Is.","+599")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("新喀里多尼亚群岛","New Caledonia Is.","+687")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("新西兰","New Zealand","+64")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("尼加拉瓜","Nicaragua","+505")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("尼日尔","Niger","+227")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("尼日利亚","Nigeria","+234")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("纽埃岛","Niue I.","+683")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("诺福克岛","Norfolk I,","+6723")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("挪威","Norway","+47")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿曼","Oman","+968")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("帕劳","Palau","+680")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴拿马","Panama","+507")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴布亚新几内亚","Papua New Guinea","+675")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴拉圭","Paraguay","+595")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("秘鲁","Peru","+51")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("菲律宾","Philippines","+63")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("波兰","Poland","+48")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("葡萄牙","Portugal","+351")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("巴基斯坦","Pskistan","+92")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("波多黎各","Puerto Rico","+1787")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("卡塔尔","Qatar","+974")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("留尼汪岛","Reunion I.","+262")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("罗马尼亚","Rumania","+40")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("俄罗斯","Russia","+7")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("卢旺达","Rwanda","+250")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("东萨摩亚","Samoa ,Eastern","+684")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("西萨摩亚","Samoa ,Western","+685")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣马力诺","San.Marino","+378")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣皮埃尔岛及密克隆岛","San.Pierre And Miquelon I.","+508")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣多美和普林西比","San.Tome And Principe","+239")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("沙特阿拉伯","Saudi Arabia","+966")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("塞内加尔","Senegal","+221")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("塞舌尔","Seychelles","+248")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("新加坡","Singapore","+65")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("斯洛伐克","Slovak","+421")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("斯洛文尼亚","Slovenia","+386")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("所罗门群岛","Solomon Is.","+677")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("索马里","Somali","+252")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("南非","South Africa","+27")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("西班牙","Spain","+34")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("斯里兰卡","Sri Lanka","+94")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣克里斯托弗和尼维斯","St.Christopher and Nevis","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣赫勒拿","St.Helena","+290")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣卢西亚","St.Lucia","+1758")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("圣文森特岛","St.Vincent I.","+1784")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("苏丹","Sudan","+249")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("苏里南","Suriname","+597")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("斯威士兰","Swaziland","+268")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瑞典","Sweden","+46")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瑞士","Switzerland","+41")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("叙利亚","Syria","+963")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("塔吉克斯坦","Tajikistan","+7")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("坦桑尼亚","Tanzania","+255")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("泰国","Thailand","+66")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("阿拉伯联合酋长国","The United Arab Emirates","+971")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("多哥","Togo","+228")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("托克劳群岛","Tokelau Is.","+690")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("汤加","Tonga","+676")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("特立尼达和多巴哥","Trinidad and Tobago","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("突尼斯","Tunisia","+216")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("土耳其","Turkey","+90")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("土库曼斯坦","Turkmenistan","+993")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("特克斯和凯科斯群岛","Turks and Caicos Is.","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("图瓦卢","Tuvalu","+688")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("乌干达","Uganda","+256")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("乌克兰","Ukraine","+380")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("英国","United Kingdom","+44")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("乌拉圭","Uruguay","+598")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("乌兹别克斯坦","Uzbekistan","+7")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瓦努阿图","Vanuatu","+678")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("梵蒂冈","Vatican","+379")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("委内瑞拉","Venezuela","+58")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("越南","Vietnam","+84")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("维尔京群岛","Virgin Is.","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("维尔京群岛和圣罗克伊","Virgin Is. and St.Croix I.","+1809")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("威克岛","Wake I.","+1808")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("瓦里斯和富士那群岛","Wallis And Futuna Is.","+681")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("西撒哈拉","Western sahara","+967")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("也门","Yemen","+967")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("南斯拉夫","Yugoslavia","+381")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("扎伊尔","Zaire","+243")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("赞比亚","Zambia","+260")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("桑给巴尔","Zanzibar","+259")');
        $this->addSql('INSERT INTO UserPhoneCode(`cnName`,`enName`,`code`) VALUES("津巴布韦","Zimbabwe","+263")');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE UserPhoneCode');
    }
}