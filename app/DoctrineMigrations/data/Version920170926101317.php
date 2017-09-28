<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170926101317 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $columns = array('start_date','cycle','monthly_rent','number','creation_date','total_rent','end_date','deposit','amount',
            'send_date','revised_amount','price','discount_price','base_price','event_start_date','payment_date','application_time','view_time','grab_date','ID');
        $query = $em->getRepository('SandboxApiBundle:GenericList\GenericList')
                        ->createQueryBuilder('g')
                        ->where('g.column IN (:columns)')
                        ->setParameter('columns',$columns);

        $genericlists = $query->getQuery()->getResult();

        foreach($genericlists as $genericlist){
            $genericlist->setSort(true);
        }

        $em->flush();
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
