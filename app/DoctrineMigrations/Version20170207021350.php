<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170207021350 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $chinaCountry = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '中国'));

        $tianjinP = new RoomCity();
        $tianjinP->setName('天津');
        $tianjinP->setParent($chinaCountry);
        $tianjinP->setLevel(2);
        $tianjinP->setEnName(null);
        $tianjinP->setKey(null);
        $tianjinP->setCapital(false);
        $em->persist($tianjinP);

        $tianjinC = new RoomCity();
        $tianjinC->setName('天津市');
        $tianjinC->setParent($tianjinP);
        $tianjinC->setLevel(3);
        $tianjinC->setEnName('Tianjin');
        $tianjinC->setKey('TNJ');
        $tianjinC->setCapital(false);
        $em->persist($tianjinC);

        $tianjinD1 = new RoomCity();
        $tianjinD1->setName('和平区');
        $tianjinD1->setParent($tianjinC);
        $tianjinD1->setLevel(4);
        $tianjinD1->setCapital(false);
        $em->persist($tianjinD1);

        $tianjinD2 = new RoomCity();
        $tianjinD2->setName('河东区');
        $tianjinD2->setParent($tianjinC);
        $tianjinD2->setLevel(4);
        $tianjinD2->setCapital(false);
        $em->persist($tianjinD2);

        $tianjinD3 = new RoomCity();
        $tianjinD3->setName('河西区');
        $tianjinD3->setParent($tianjinC);
        $tianjinD3->setLevel(4);
        $tianjinD3->setCapital(false);
        $em->persist($tianjinD3);

        $tianjinD4 = new RoomCity();
        $tianjinD4->setName('南开区');
        $tianjinD4->setParent($tianjinC);
        $tianjinD4->setLevel(4);
        $tianjinD4->setCapital(false);
        $em->persist($tianjinD4);

        $tianjinD5 = new RoomCity();
        $tianjinD5->setName('河北区');
        $tianjinD5->setParent($tianjinC);
        $tianjinD5->setLevel(4);
        $tianjinD5->setCapital(false);
        $em->persist($tianjinD5);

        $tianjinD6 = new RoomCity();
        $tianjinD6->setName('红桥区');
        $tianjinD6->setParent($tianjinC);
        $tianjinD6->setLevel(4);
        $tianjinD6->setCapital(false);
        $em->persist($tianjinD6);

        $tianjinD7 = new RoomCity();
        $tianjinD7->setName('滨海新区');
        $tianjinD7->setParent($tianjinC);
        $tianjinD7->setLevel(4);
        $tianjinD7->setCapital(false);
        $em->persist($tianjinD7);

        $tianjinD8 = new RoomCity();
        $tianjinD8->setName('东丽区');
        $tianjinD8->setParent($tianjinC);
        $tianjinD8->setLevel(4);
        $tianjinD8->setCapital(false);
        $em->persist($tianjinD8);

        $tianjinD9 = new RoomCity();
        $tianjinD9->setName('西青区');
        $tianjinD9->setParent($tianjinC);
        $tianjinD9->setLevel(4);
        $tianjinD9->setCapital(false);
        $em->persist($tianjinD9);

        $tianjinD10 = new RoomCity();
        $tianjinD10->setName('津南区');
        $tianjinD10->setParent($tianjinC);
        $tianjinD10->setLevel(4);
        $tianjinD10->setCapital(false);
        $em->persist($tianjinD10);

        $tianjinD11 = new RoomCity();
        $tianjinD11->setName('北辰区');
        $tianjinD11->setParent($tianjinC);
        $tianjinD11->setLevel(4);
        $tianjinD11->setCapital(false);
        $em->persist($tianjinD11);

        $tianjinD12 = new RoomCity();
        $tianjinD12->setName('武清区');
        $tianjinD12->setParent($tianjinC);
        $tianjinD12->setLevel(4);
        $tianjinD12->setCapital(false);
        $em->persist($tianjinD12);

        $tianjinD13 = new RoomCity();
        $tianjinD13->setName('宝坻区');
        $tianjinD13->setParent($tianjinC);
        $tianjinD13->setLevel(4);
        $tianjinD13->setCapital(false);
        $em->persist($tianjinD13);

        $tianjinD14 = new RoomCity();
        $tianjinD14->setName('宁河区');
        $tianjinD14->setParent($tianjinC);
        $tianjinD14->setLevel(4);
        $tianjinD14->setCapital(false);
        $em->persist($tianjinD14);

        $tianjinD15 = new RoomCity();
        $tianjinD15->setName('静海区');
        $tianjinD15->setParent($tianjinC);
        $tianjinD15->setLevel(4);
        $tianjinD15->setCapital(false);
        $em->persist($tianjinD15);

        $tianjinD16 = new RoomCity();
        $tianjinD16->setName('蓟州区');
        $tianjinD16->setParent($tianjinC);
        $tianjinD16->setLevel(4);
        $tianjinD16->setCapital(false);
        $em->persist($tianjinD16);

        $dalianP = new RoomCity();
        $dalianP->setName('辽宁省');
        $dalianP->setParent($chinaCountry);
        $dalianP->setLevel(2);
        $dalianP->setCapital(false);
        $em->persist($dalianP);

        $dalianC = new RoomCity();
        $dalianC->setName('大连市');
        $dalianC->setParent($dalianP);
        $dalianC->setLevel(3);
        $dalianC->setEnName('Dalian');
        $dalianC->setKey('DLC');
        $dalianC->setCapital(false);
        $em->persist($dalianC);

        $dalianD1 = new RoomCity();
        $dalianD1->setName('中山区');
        $dalianD1->setParent($dalianC);
        $dalianD1->setLevel(4);
        $dalianD1->setCapital(false);
        $em->persist($dalianD1);

        $dalianD2 = new RoomCity();
        $dalianD2->setName('西岚区');
        $dalianD2->setParent($dalianC);
        $dalianD2->setLevel(4);
        $dalianD2->setCapital(false);
        $em->persist($dalianD2);

        $dalianD3 = new RoomCity();
        $dalianD3->setName('沙河口区');
        $dalianD3->setParent($dalianC);
        $dalianD3->setLevel(4);
        $dalianD3->setCapital(false);
        $em->persist($dalianD3);

        $dalianD4 = new RoomCity();
        $dalianD4->setName('甘井子区');
        $dalianD4->setParent($dalianC);
        $dalianD4->setLevel(4);
        $dalianD4->setCapital(false);
        $em->persist($dalianD4);

        $dalianD5 = new RoomCity();
        $dalianD5->setName('旅顺口区');
        $dalianD5->setParent($dalianC);
        $dalianD5->setLevel(4);
        $dalianD5->setCapital(false);
        $em->persist($dalianD5);

        $dalianD6 = new RoomCity();
        $dalianD6->setName('金州区');
        $dalianD6->setParent($dalianC);
        $dalianD6->setLevel(4);
        $dalianD6->setCapital(false);
        $em->persist($dalianD6);

        $dalianD7 = new RoomCity();
        $dalianD7->setName('长海县');
        $dalianD7->setParent($dalianC);
        $dalianD7->setLevel(4);
        $dalianD7->setCapital(false);
        $em->persist($dalianD7);

        $dalianD8 = new RoomCity();
        $dalianD8->setName('瓦房店市');
        $dalianD8->setParent($dalianC);
        $dalianD8->setLevel(4);
        $dalianD8->setCapital(false);
        $em->persist($dalianD8);

        $dalianD9 = new RoomCity();
        $dalianD9->setName('普兰店区');
        $dalianD9->setParent($dalianC);
        $dalianD9->setLevel(4);
        $dalianD9->setCapital(false);
        $em->persist($dalianD9);

        $dalianD10 = new RoomCity();
        $dalianD10->setName('庄河市');
        $dalianD10->setParent($dalianC);
        $dalianD10->setLevel(4);
        $dalianD10->setCapital(false);
        $em->persist($dalianD10);

        $harbinP = new RoomCity();
        $harbinP->setName('黑龙江省');
        $harbinP->setParent($chinaCountry);
        $harbinP->setLevel(2);
        $harbinP->setCapital(false);
        $em->persist($harbinP);

        $harbinC = new RoomCity();
        $harbinC->setName('哈尔滨市');
        $harbinC->setParent($harbinP);
        $harbinC->setLevel(3);
        $harbinC->setEnName('Harbin');
        $harbinC->setKey('HRB');
        $harbinC->setCapital(false);
        $em->persist($harbinC);

        $harbinD1 = new RoomCity();
        $harbinD1->setName('道里区');
        $harbinD1->setParent($harbinC);
        $harbinD1->setLevel(4);
        $harbinD1->setCapital(false);
        $em->persist($harbinD1);

        $harbinD2 = new RoomCity();
        $harbinD2->setName('南岗区');
        $harbinD2->setParent($harbinC);
        $harbinD2->setLevel(4);
        $harbinD2->setCapital(false);
        $em->persist($harbinD2);

        $harbinD3 = new RoomCity();
        $harbinD3->setName('道外区');
        $harbinD3->setParent($harbinC);
        $harbinD3->setLevel(4);
        $harbinD3->setCapital(false);
        $em->persist($harbinD3);

        $harbinD4 = new RoomCity();
        $harbinD4->setName('平房区');
        $harbinD4->setParent($harbinC);
        $harbinD4->setLevel(4);
        $harbinD4->setCapital(false);
        $em->persist($harbinD4);

        $harbinD5 = new RoomCity();
        $harbinD5->setName('松北区');
        $harbinD5->setParent($harbinC);
        $harbinD5->setLevel(4);
        $harbinD5->setCapital(false);
        $em->persist($harbinD5);

        $harbinD6 = new RoomCity();
        $harbinD6->setName('香坊区');
        $harbinD6->setParent($harbinC);
        $harbinD6->setLevel(4);
        $harbinD6->setCapital(false);
        $em->persist($harbinD6);

        $harbinD7 = new RoomCity();
        $harbinD7->setName('呼兰区');
        $harbinD7->setParent($harbinC);
        $harbinD7->setLevel(4);
        $harbinD7->setCapital(false);
        $em->persist($harbinD7);

        $harbinD8 = new RoomCity();
        $harbinD8->setName('阿城区');
        $harbinD8->setParent($harbinC);
        $harbinD8->setLevel(4);
        $harbinD8->setCapital(false);
        $em->persist($harbinD8);

        $harbinD9 = new RoomCity();
        $harbinD9->setName('双城区');
        $harbinD9->setParent($harbinC);
        $harbinD9->setLevel(4);
        $harbinD9->setCapital(false);
        $em->persist($harbinD9);

        $hefeiP = new RoomCity();
        $hefeiP->setName('安徽省');
        $hefeiP->setParent($chinaCountry);
        $hefeiP->setLevel(2);
        $hefeiP->setCapital(false);
        $em->persist($hefeiP);

        $hefeiC = new RoomCity();
        $hefeiC->setName('合肥市');
        $hefeiC->setParent($hefeiP);
        $hefeiC->setLevel(3);
        $hefeiC->setEnName('Hefei');
        $hefeiC->setKey('HFE');
        $hefeiC->setCapital(false);
        $em->persist($hefeiC);

        $hefeiD1 = new RoomCity();
        $hefeiD1->setName('瑶海区');
        $hefeiD1->setParent($hefeiC);
        $hefeiD1->setLevel(4);
        $hefeiD1->setCapital(false);
        $em->persist($hefeiD1);

        $hefeiD2 = new RoomCity();
        $hefeiD2->setName('庐阳区');
        $hefeiD2->setParent($hefeiC);
        $hefeiD2->setLevel(4);
        $hefeiD2->setCapital(false);
        $em->persist($hefeiD2);

        $hefeiD3 = new RoomCity();
        $hefeiD3->setName('蜀山区');
        $hefeiD3->setParent($hefeiC);
        $hefeiD3->setLevel(4);
        $hefeiD3->setCapital(false);
        $em->persist($hefeiD3);

        $hefeiD4 = new RoomCity();
        $hefeiD4->setName('包河区');
        $hefeiD4->setParent($hefeiC);
        $hefeiD4->setLevel(4);
        $hefeiD4->setCapital(false);
        $em->persist($hefeiD4);

        $jiangsuP = new RoomCity();
        $jiangsuP->setName('江苏省');
        $jiangsuP->setParent($chinaCountry);
        $jiangsuP->setLevel(2);
        $jiangsuP->setCapital(false);
        $em->persist($jiangsuP);

        $nanjingC = new RoomCity();
        $nanjingC->setName('南京市');
        $nanjingC->setParent($jiangsuP);
        $nanjingC->setLevel(3);
        $nanjingC->setEnName('Nanjing');
        $nanjingC->setKey('NJ');
        $nanjingC->setCapital(false);
        $em->persist($nanjingC);

        $nanjingD1 = new RoomCity();
        $nanjingD1->setName('玄武区');
        $nanjingD1->setParent($nanjingC);
        $nanjingD1->setLevel(4);
        $nanjingD1->setCapital(false);
        $em->persist($nanjingD1);

        $nanjingD2 = new RoomCity();
        $nanjingD2->setName('秦淮区');
        $nanjingD2->setParent($nanjingC);
        $nanjingD2->setLevel(4);
        $nanjingD2->setCapital(false);
        $em->persist($nanjingD2);

        $nanjingD3 = new RoomCity();
        $nanjingD3->setName('鼓楼区');
        $nanjingD3->setParent($nanjingC);
        $nanjingD3->setLevel(4);
        $nanjingD3->setCapital(false);
        $em->persist($nanjingD3);

        $nanjingD4 = new RoomCity();
        $nanjingD4->setName('建邺区');
        $nanjingD4->setParent($nanjingC);
        $nanjingD4->setLevel(4);
        $nanjingD4->setCapital(false);
        $em->persist($nanjingD4);

        $nanjingD5 = new RoomCity();
        $nanjingD5->setName('雨花台区');
        $nanjingD5->setParent($nanjingC);
        $nanjingD5->setLevel(4);
        $nanjingD5->setCapital(false);
        $em->persist($nanjingD5);

        $nanjingD6 = new RoomCity();
        $nanjingD6->setName('栖霞区');
        $nanjingD6->setParent($nanjingC);
        $nanjingD6->setLevel(4);
        $nanjingD6->setCapital(false);
        $em->persist($nanjingD6);

        $nanjingD7 = new RoomCity();
        $nanjingD7->setName('浦口区');
        $nanjingD7->setParent($nanjingC);
        $nanjingD7->setLevel(4);
        $nanjingD7->setCapital(false);
        $em->persist($nanjingD7);

        $nanjingD8 = new RoomCity();
        $nanjingD8->setName('六合区');
        $nanjingD8->setParent($nanjingC);
        $nanjingD8->setLevel(4);
        $nanjingD8->setCapital(false);
        $em->persist($nanjingD8);

        $nanjingD9 = new RoomCity();
        $nanjingD9->setName('江宁区');
        $nanjingD9->setParent($nanjingC);
        $nanjingD9->setLevel(4);
        $nanjingD9->setCapital(false);
        $em->persist($nanjingD9);

        $nanjingD10 = new RoomCity();
        $nanjingD10->setName('溧水区');
        $nanjingD10->setParent($nanjingC);
        $nanjingD10->setLevel(4);
        $nanjingD10->setCapital(false);
        $em->persist($nanjingD10);

        $nanjingD11 = new RoomCity();
        $nanjingD11->setName('高淳区');
        $nanjingD11->setParent($nanjingC);
        $nanjingD11->setLevel(4);
        $nanjingD11->setCapital(false);
        $em->persist($nanjingD11);

        $suzhouC = new RoomCity();
        $suzhouC->setName('苏州市');
        $suzhouC->setParent($jiangsuP);
        $suzhouC->setLevel(3);
        $suzhouC->setEnName('Suzhou');
        $suzhouC->setKey('SZV');
        $suzhouC->setCapital(false);
        $em->persist($suzhouC);

        $suzhouD1 = new RoomCity();
        $suzhouD1->setName('姑苏区');
        $suzhouD1->setParent($suzhouC);
        $suzhouD1->setLevel(4);
        $suzhouD1->setCapital(false);
        $em->persist($suzhouD1);

        $suzhouD2 = new RoomCity();
        $suzhouD2->setName('虎丘区');
        $suzhouD2->setParent($suzhouC);
        $suzhouD2->setLevel(4);
        $suzhouD2->setCapital(false);
        $em->persist($suzhouD2);

        $suzhouD3 = new RoomCity();
        $suzhouD3->setName('吴中区');
        $suzhouD3->setParent($suzhouC);
        $suzhouD3->setLevel(4);
        $suzhouD3->setCapital(false);
        $em->persist($suzhouD3);

        $suzhouD4 = new RoomCity();
        $suzhouD4->setName('相城区');
        $suzhouD4->setParent($suzhouC);
        $suzhouD4->setLevel(4);
        $suzhouD4->setCapital(false);
        $em->persist($suzhouD4);

        $suzhouD5 = new RoomCity();
        $suzhouD5->setName('吴江区');
        $suzhouD5->setParent($suzhouC);
        $suzhouD5->setLevel(4);
        $suzhouD5->setCapital(false);
        $em->persist($suzhouD5);

        $wuxiC = new RoomCity();
        $wuxiC->setName('无锡市');
        $wuxiC->setParent($jiangsuP);
        $wuxiC->setLevel(3);
        $wuxiC->setEnName('Wuxi');
        $wuxiC->setKey('WX');
        $wuxiC->setCapital(false);
        $em->persist($wuxiC);

        $wuxiD1 = new RoomCity();
        $wuxiD1->setName('梁溪区');
        $wuxiD1->setParent($wuxiC);
        $wuxiD1->setLevel(4);
        $wuxiD1->setCapital(false);
        $em->persist($wuxiD1);

        $wuxiD2 = new RoomCity();
        $wuxiD2->setName('滨湖区');
        $wuxiD2->setParent($wuxiC);
        $wuxiD2->setLevel(4);
        $wuxiD2->setCapital(false);
        $em->persist($wuxiD2);

        $wuxiD3 = new RoomCity();
        $wuxiD3->setName('惠山区');
        $wuxiD3->setParent($wuxiC);
        $wuxiD3->setLevel(4);
        $wuxiD3->setCapital(false);
        $em->persist($wuxiD3);

        $wuxiD4 = new RoomCity();
        $wuxiD4->setName('锡山区');
        $wuxiD4->setParent($wuxiC);
        $wuxiD4->setLevel(4);
        $wuxiD4->setCapital(false);
        $em->persist($wuxiD4);

        $wuxiD5 = new RoomCity();
        $wuxiD5->setName('新吴区');
        $wuxiD5->setParent($wuxiC);
        $wuxiD5->setLevel(4);
        $wuxiD5->setCapital(false);
        $em->persist($wuxiD5);

        $zhejiangP = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '浙江省'));

        $ningboC = new RoomCity();
        $ningboC->setName('宁波市');
        $ningboC->setParent($zhejiangP);
        $ningboC->setLevel(3);
        $ningboC->setEnName('Ningbo');
        $ningboC->setKey('NBO');
        $ningboC->setCapital(false);
        $em->persist($ningboC);

        $ningboD1 = new RoomCity();
        $ningboD1->setName('海曙区');
        $ningboD1->setParent($ningboC);
        $ningboD1->setLevel(4);
        $ningboD1->setCapital(false);
        $em->persist($ningboD1);

        $ningboD2 = new RoomCity();
        $ningboD2->setName('江北区');
        $ningboD2->setParent($ningboC);
        $ningboD2->setLevel(4);
        $ningboD2->setCapital(false);
        $em->persist($ningboD2);

        $ningboD3 = new RoomCity();
        $ningboD3->setName('北仑区');
        $ningboD3->setParent($ningboC);
        $ningboD3->setLevel(4);
        $ningboD3->setCapital(false);
        $em->persist($ningboD3);

        $ningboD4 = new RoomCity();
        $ningboD4->setName('镇海区');
        $ningboD4->setParent($ningboC);
        $ningboD4->setLevel(4);
        $ningboD4->setCapital(false);
        $em->persist($ningboD4);

        $ningboD5 = new RoomCity();
        $ningboD5->setName('鄞州区');
        $ningboD5->setParent($ningboC);
        $ningboD5->setLevel(4);
        $ningboD5->setCapital(false);
        $em->persist($ningboD5);

        $ningboD6 = new RoomCity();
        $ningboD6->setName('奉化区');
        $ningboD6->setParent($ningboC);
        $ningboD6->setLevel(4);
        $ningboD6->setCapital(false);
        $em->persist($ningboD6);

        $jiaxingC = new RoomCity();
        $jiaxingC->setName('嘉兴市');
        $jiaxingC->setParent($zhejiangP);
        $jiaxingC->setLevel(3);
        $jiaxingC->setEnName('Jiaxing');
        $jiaxingC->setKey('JIAXING');
        $jiaxingC->setCapital(false);
        $em->persist($jiaxingC);

        $jiaxingD1 = new RoomCity();
        $jiaxingD1->setName('南湖区');
        $jiaxingD1->setParent($jiaxingC);
        $jiaxingD1->setLevel(4);
        $jiaxingD1->setCapital(false);
        $em->persist($jiaxingD1);

        $jiaxingD2 = new RoomCity();
        $jiaxingD2->setName('秀洲区');
        $jiaxingD2->setParent($jiaxingC);
        $jiaxingD2->setLevel(4);
        $jiaxingD2->setCapital(false);
        $em->persist($jiaxingD2);

        $chengduP = new RoomCity();
        $chengduP->setName('四川省');
        $chengduP->setParent($chinaCountry);
        $chengduP->setLevel(2);
        $chengduP->setCapital(false);
        $em->persist($chengduP);

        $chengduC = new RoomCity();
        $chengduC->setName('成都市');
        $chengduC->setParent($chengduP);
        $chengduC->setLevel(3);
        $chengduC->setEnName('Chengdu');
        $chengduC->setKey('CD');
        $chengduC->setCapital(false);
        $em->persist($chengduC);

        $chengduD1 = new RoomCity();
        $chengduD1->setName('武侯区');
        $chengduD1->setParent($chengduC);
        $chengduD1->setLevel(4);
        $chengduD1->setCapital(false);
        $em->persist($chengduD1);

        $chengduD2 = new RoomCity();
        $chengduD2->setName('锦江区');
        $chengduD2->setParent($chengduC);
        $chengduD2->setLevel(4);
        $chengduD2->setCapital(false);
        $em->persist($chengduD2);

        $chengduD3 = new RoomCity();
        $chengduD3->setName('青羊区');
        $chengduD3->setParent($chengduC);
        $chengduD3->setLevel(4);
        $chengduD3->setCapital(false);
        $em->persist($chengduD3);

        $chengduD4 = new RoomCity();
        $chengduD4->setName('金牛区');
        $chengduD4->setParent($chengduC);
        $chengduD4->setLevel(4);
        $chengduD4->setCapital(false);
        $em->persist($chengduD4);

        $chengduD5 = new RoomCity();
        $chengduD5->setName('成华区');
        $chengduD5->setParent($chengduC);
        $chengduD5->setLevel(4);
        $chengduD5->setCapital(false);
        $em->persist($chengduD5);

        $chengduD6 = new RoomCity();
        $chengduD6->setName('龙泉驿区');
        $chengduD6->setParent($chengduC);
        $chengduD6->setLevel(4);
        $chengduD6->setCapital(false);
        $em->persist($chengduD6);

        $chengduD7 = new RoomCity();
        $chengduD7->setName('温江区');
        $chengduD7->setParent($chengduC);
        $chengduD7->setLevel(4);
        $chengduD7->setCapital(false);
        $em->persist($chengduD7);

        $chengduD8 = new RoomCity();
        $chengduD8->setName('新都区');
        $chengduD8->setParent($chengduC);
        $chengduD8->setLevel(4);
        $chengduD8->setCapital(false);
        $em->persist($chengduD8);

        $chengduD9 = new RoomCity();
        $chengduD9->setName('青白江区');
        $chengduD9->setParent($chengduC);
        $chengduD9->setLevel(4);
        $chengduD9->setCapital(false);
        $em->persist($chengduD9);

        $chengduD10 = new RoomCity();
        $chengduD10->setName('双流区');
        $chengduD10->setParent($chengduC);
        $chengduD10->setLevel(4);
        $chengduD10->setCapital(false);
        $em->persist($chengduD10);

        $chengduD11 = new RoomCity();
        $chengduD11->setName('郫都区');
        $chengduD11->setParent($chengduC);
        $chengduD11->setLevel(4);
        $chengduD11->setCapital(false);
        $em->persist($chengduD11);

        $changshaP = new RoomCity();
        $changshaP->setName('湖南省');
        $changshaP->setParent($chinaCountry);
        $changshaP->setLevel(2);
        $changshaP->setCapital(false);
        $em->persist($changshaP);

        $changshaC = new RoomCity();
        $changshaC->setName('长沙市');
        $changshaC->setParent($changshaP);
        $changshaC->setLevel(3);
        $changshaC->setEnName('Changsha');
        $changshaC->setKey('CSX');
        $changshaC->setCapital(false);
        $em->persist($changshaC);

        $changshaD1 = new RoomCity();
        $changshaD1->setName('芙蓉区');
        $changshaD1->setParent($changshaC);
        $changshaD1->setLevel(4);
        $changshaD1->setCapital(false);
        $em->persist($changshaD1);

        $changshaD2 = new RoomCity();
        $changshaD2->setName('天心区');
        $changshaD2->setParent($changshaC);
        $changshaD2->setLevel(4);
        $changshaD2->setCapital(false);
        $em->persist($changshaD2);

        $changshaD3 = new RoomCity();
        $changshaD3->setName('岳麓区');
        $changshaD3->setParent($changshaC);
        $changshaD3->setLevel(4);
        $changshaD3->setCapital(false);
        $em->persist($changshaD3);

        $changshaD4 = new RoomCity();
        $changshaD4->setName('开福区');
        $changshaD4->setParent($changshaC);
        $changshaD4->setLevel(4);
        $changshaD4->setCapital(false);
        $em->persist($changshaD4);

        $changshaD5 = new RoomCity();
        $changshaD5->setName('雨花区');
        $changshaD5->setParent($changshaC);
        $changshaD5->setLevel(4);
        $changshaD5->setCapital(false);
        $em->persist($changshaD5);

        $changshaD6 = new RoomCity();
        $changshaD6->setName('望城区');
        $changshaD6->setParent($changshaC);
        $changshaD6->setLevel(4);
        $changshaD6->setCapital(false);
        $em->persist($changshaD6);

        $changshaD7 = new RoomCity();
        $changshaD7->setName('浏阳市');
        $changshaD7->setParent($changshaC);
        $changshaD7->setLevel(4);
        $changshaD7->setCapital(false);
        $em->persist($changshaD7);

        $changshaD8 = new RoomCity();
        $changshaD8->setName('长沙县');
        $changshaD8->setParent($changshaC);
        $changshaD8->setLevel(4);
        $changshaD8->setCapital(false);
        $em->persist($changshaD8);

        $changshaD9 = new RoomCity();
        $changshaD9->setName('宁乡县');
        $changshaD9->setParent($changshaC);
        $changshaD9->setLevel(4);
        $changshaD9->setCapital(false);
        $em->persist($changshaD9);

        $wuhanP = new RoomCity();
        $wuhanP->setName('湖北省');
        $wuhanP->setParent($chinaCountry);
        $wuhanP->setLevel(2);
        $wuhanP->setCapital(false);
        $em->persist($wuhanP);

        $wuhanC = new RoomCity();
        $wuhanC->setName('武汉市');
        $wuhanC->setParent($wuhanP);
        $wuhanC->setLevel(3);
        $wuhanC->setEnName('Wuhan');
        $wuhanC->setKey('WUH');
        $wuhanC->setCapital(false);
        $em->persist($wuhanC);

        $wuhanD1 = new RoomCity();
        $wuhanD1->setName('江岸区');
        $wuhanD1->setParent($wuhanC);
        $wuhanD1->setLevel(4);
        $wuhanD1->setCapital(false);
        $em->persist($wuhanD1);

        $wuhanD2 = new RoomCity();
        $wuhanD2->setName('江汉区');
        $wuhanD2->setParent($wuhanC);
        $wuhanD2->setLevel(4);
        $wuhanD2->setCapital(false);
        $em->persist($wuhanD2);

        $wuhanD3 = new RoomCity();
        $wuhanD3->setName('硚口区');
        $wuhanD3->setParent($wuhanC);
        $wuhanD3->setLevel(4);
        $wuhanD3->setCapital(false);
        $em->persist($wuhanD3);

        $wuhanD4 = new RoomCity();
        $wuhanD4->setName('汉阳区');
        $wuhanD4->setParent($wuhanC);
        $wuhanD4->setLevel(4);
        $wuhanD4->setCapital(false);
        $em->persist($wuhanD4);

        $wuhanD5 = new RoomCity();
        $wuhanD5->setName('武昌区');
        $wuhanD5->setParent($wuhanC);
        $wuhanD5->setLevel(4);
        $wuhanD5->setCapital(false);
        $em->persist($wuhanD5);

        $wuhanD6 = new RoomCity();
        $wuhanD6->setName('青山区');
        $wuhanD6->setParent($wuhanC);
        $wuhanD6->setLevel(4);
        $wuhanD6->setCapital(false);
        $em->persist($wuhanD6);

        $wuhanD7 = new RoomCity();
        $wuhanD7->setName('洪山区');
        $wuhanD7->setParent($wuhanC);
        $wuhanD7->setLevel(4);
        $wuhanD7->setCapital(false);
        $em->persist($wuhanD7);

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
