<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160909170928 extends AbstractMigration
{
    // change table names
    private $tableNames = array(
        'Admin' => 'admin',
        'AdminClient' => 'admin_client',
        'AdminPermission' => 'admin_permission',
        'AdminPermissionMap' => 'admin_permission_map',
        'AdminToken' => 'admin_token',
        'AdminType' => 'admin_types',
        'Advertising' => 'advertising',
        'AdvertisingAttachment' => 'advertising_attachment',
        'Announcement' => 'announcement',
        'AppInfo' => 'app_info',
        'Banner' => 'banner',
        'BannerTag' => 'banner_tag',
        'Buddy' => 'buddy',
        'BuddyRequest' => 'buddy_request',
        'BulletinPost' => 'bulletin_post',
        'BulletinPostAttachment' => 'bulletin_post_attachment',
        'BulletinType' => 'bulletin_types',
        'ChatGroup' => 'chat_group',
        'ChatGroupMember' => 'chat_group_member',
        'Company' => 'company',
        'CompanyIndustry' => 'company_industry',
        'CompanyIndustryMap' => 'company_industry_map',
        'CompanyInvitation' => 'company_invitation',
        'CompanyMember' => 'company_member',
        'CompanyPortfolio' => 'company_portfolio',
        'CompanyVerifyRecord' => 'company_verify_record',
        'DoorAccess' => 'door_access',
        'Event' => 'event',
        'EventAttachment' => 'event_attachment',
        'EventComment' => 'event_comment',
        'EventDate' => 'event_dates',
        'EventForm' => 'event_form',
        'EventFormOption' => 'event_form_option',
        'EventLike' => 'event_likes',
        'EventOrder' => 'event_order',
        'EventRegistration' => 'event_registration',
        'EventRegistrationCheck' => 'event_registration_check',
        'EventRegistrationForm' => 'event_registration_form',
        'EventTime' => 'event_times',
        'Feature' => 'features',
        'Feed' => 'feed',
        'FeedAttachment' => 'feed_attachment',
        'FeedComment' => 'feed_comment',
        'FeedLike' => 'feed_likes',
        'Food' => 'food',
        'FoodAttachment' => 'food_attachment',
        'FoodForm' => 'food_form',
        'FoodFormOption' => 'food_form_option',
        'FoodItem' => 'food_item',
        'FoodItemOption' => 'food_item_option',
        'FoodOrder' => 'food_order',
        'FoodOrderPost' => 'food_order_post',
        'Log' => 'log',
        'LogModules' => 'log_modules',
        'Menu' => 'menu',
        'Message' => 'messages',
        'MessagePush' => 'message_push',
        'News' => 'news',
        'NewsAttachment' => 'news_attachment',
        'InvitedPeople' => 'invited_persons',
        'MembershipOrder' => 'membership_order',
        'OrderCount' => 'order_count',
        'OrderMap' => 'order_map',
        'OrderOfflineTransfer' => 'order_offline_transfer',
        'ProductOrder' => 'product_order',
        'ProductOrderCheck' => 'product_order_check',
        'ProductOrderRecord' => 'product_order_record',
        'TopUpOrder' => 'top_up_order',
        'TransferAttachment' => 'transfer_attachment',
        'Product' => 'product',
        'ProductAppointment' => 'product_appointment',
        'ClientRandomRecord' => 'client_random_record',
        'Room' => 'room',
        'RoomAttachment' => 'room_attachment',
        'RoomAttachmentBinding' => 'room_attachment_binding',
        'RoomBuilding' => 'room_building',
        'RoomBuildingAttachment' => 'room_building_attachment',
        'RoomBuildingCompany' => 'room_building_company',
        'RoomBuildingPhones' => 'room_building_phones',
        'RoomBuildingServiceBinding' => 'room_building_service_binding',
        'RoomBuildingServices' => 'room_building_services',
        'RoomBuildingTag' => 'room_building_tag',
        'RoomBuildingTagBinding' => 'room_building_tag_binding',
        'RoomBuildingTypeBinding' => 'room_building_type_binding',
        'RoomCity' => 'room_city',
        'RoomDoors' => 'room_doors',
        'RoomFixed' => 'room_fixed',
        'RoomFloor' => 'room_floor',
        'RoomMeeting' => 'room_meeting',
        'RoomSupplies' => 'room_supplies',
        'RoomTypes' => 'room_types',
        'RoomTypeUnit' => 'room_type_unit',
        'Supplies' => 'supplies',
        'SalesAdmin' => 'sales_admin',
        'SalesAdminClient' => 'sales_admin_client',
        'SalesAdminExcludePermission' => 'sales_admin_exclude_permission',
        'SalesAdminPermission' => 'sales_admin_permission',
        'SalesAdminPermissionMap' => 'sales_admin_permission_map',
        'SalesAdminToken' => 'sales_admin_token',
        'SalesAdminType' => 'sales_admin_types',
        'SalesCompany' => 'sales_company',
        'SalesCompanyUserCard' => 'sales_company_user_card',
        'SalesUser' => 'sales_user',
        'OpenServer' => 'open_server',
        'Shop' => 'shop',
        'ShopAdmin' => 'shop_admin',
        'ShopAdminClient' => 'shop_admin_client',
        'ShopAdminPermission' => 'shop_admin_permission',
        'ShopAdminPermissionMap' => 'shop_admin_permission_map',
        'ShopAdminToken' => 'shop_admin_token',
        'ShopAdminType' => 'shop_admin_types',
        'ShopAttachment' => 'shop_attachment',
        'ShopCart' => 'shop_cart',
        'ShopMenu' => 'shop_menu',
        'ShopOrder' => 'shop_order',
        'ShopOrderProduct' => 'shop_order_product',
        'ShopOrderProductSpec' => 'shop_order_product_spec',
        'ShopOrderProductSpecItem' => 'shop_order_product_spec_item',
        'ShopProduct' => 'shop_product',
        'ShopProductAttachment' => 'shop_product_attachment',
        'ShopProductSpec' => 'shop_product_spec',
        'ShopProductSpecItem' => 'shop_product_spec_item',
        'ShopSpec' => 'shop_spec',
        'ShopSpecItem' => 'shop_spec_item',
        'WeChat' => 'we_chat',
        'WeChatShares' => 'we_chat_shares',
        'User' => 'user',
        'UserAvatar' => 'user_avatar',
        'UserBackground' => 'user_background',
        'UserClient' => 'user_client',
        'UserEducation' => 'user_education',
        'EmailVerification' => 'email_verification',
        'UserExperience' => 'user_experiences',
        'ForgetPassword' => 'user_forget_password',
        'UserHobby' => 'user_hobby',
        'UserHobbyMap' => 'user_hobby_map',
        'UserPhoneCode' => 'user_phone_codes',
        'PhoneVerification' => 'user_phone_verification',
        'UserPortfolio' => 'user_portfolio',
        'UserProfile' => 'user_profiles',
        'UserProfileMyOrders' => 'user_profile_my_orders',
        'UserProfileVisitor' => 'user_profile_visitor',
        'UserRegistration' => 'user_registration',
        'UserToken' => 'user_token',
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        foreach ($this->tableNames as $oldName => $newName) {
            $oldName = strtolower($oldName);
            if ($oldName == $newName) {
                continue;
            }
            $this->addSql("RENAME TABLE $oldName TO $newName");
        }

        $this->addSql('DROP VIEW IF EXISTS AdminApiAuthView');
        $this->addSql('DROP VIEW IF EXISTS RoomUsageView');
        $this->addSql('DROP VIEW IF EXISTS RoomView');
        $this->addSql('DROP VIEW IF EXISTS ClientApiAuthView');
        $this->addSql('DROP VIEW IF EXISTS FeedView');
        $this->addSql('DROP VIEW IF EXISTS UserView');
        $this->addSql('DROP VIEW IF EXISTS SalesAdminApiAuthView');
        $this->addSql('DROP VIEW IF EXISTS ShopAdminApiAuthView');

        $this->addSql('
            CREATE VIEW admin_api_auth_view AS
            SELECT
                t.id,
                t.token,
                t.clientId,
                a.id AS adminId,
                a.username
            FROM admin_token AS t
            JOIN admin AS a ON t.adminId = a.id
            WHERE
                t.creationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
            ;
        ');
        $this->addSql('
            CREATE VIEW room_usage_view AS
            SELECT
              id,
                productId,
                status,
                startDate,
                endDate,
                userId as user,
                appointedPerson as appointedUser
            FROM product_order
            ;
        ');
        $this->addSql('
            CREATE VIEW room_view AS
            SELECT
                r.*,
                o.status,
                o.startDate as orderStartDate,
                o.endDate as orderEndDate,
                up.userId as renterId,
                up.name as renterName,
                up.email as renterEmail
            FROM room r
            JOIN room_floor rf ON rf.id = r.floorId
            LEFT JOIN product p ON r.id = p.roomId
            LEFT JOIN product_order o ON p.id = o.productId
            LEFT JOIN user_profiles up ON o.userId = up.userId
            ;
        ');
        $this->addSql('
            CREATE VIEW client_api_auth_view AS
            SELECT
                t.id,
                t.token,
                t.clientId,
                u.id AS userId
            FROM user_token AS t
            JOIN user AS u ON t.userId = u.id
            WHERE
                t.modificationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
                AND
                u.banned != 1
                AND
                t.online = 1
            ;
        ');
        $this->addSql('
            CREATE VIEW feed_view AS
            SELECT DISTINCT f.*,
                   (SELECT COUNT(fc.id) FROM feed_comment fc LEFT JOIN user u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
                   (SELECT COUNT(fl.id) FROM feed_likes fl LEFT JOIN user u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
            FROM feed AS f;
        ');
        $this->addSql('
            CREATE VIEW user_view AS
            SELECT
                   u.id,
                   u.phone,
                   u.email,
                   u.banned,
                   u.authorized,
                   u.cardNo,
                   u.credentialNo,
                   u.authorizedPlatform,
                   u.authorizedAdminUsername,
                   up.name,
                     up.gender,
                     u.creationDate as userRegistrationDate
            FROM user u
            LEFT JOIN user_profiles up ON u.id = up.userId
            ;
        ');
        $this->addSql('
            CREATE VIEW `sales_admin_api_auth_view` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`token` AS `token`,
                `t`.`clientId` AS `clientId`,
                `a`.`id` AS `adminId`,
                `a`.`username` AS `username`
            FROM (
                `sales_admin_token` `t`
              JOIN `sales_admin` `a`
                ON((`t`.`adminId` = `a`.`id`))
                )
            WHERE (
                `t`.`creationDate` > (now() - interval 5 day)
            );
        ');
        $this->addSql('
            CREATE VIEW `shop_admin_api_auth_view` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`token` AS `token`,
                `t`.`clientId` AS `clientId`,
                `a`.`id` AS `adminId`,
                `a`.`username` AS `username`
            FROM (
                `shop_admin_token` `t`
              JOIN `shop_admin` `a`
                ON((`t`.`adminId` = `a`.`id`))
                )
            WHERE (
                `t`.`creationDate` > (now() - interval 5 day)
            );
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        foreach ($this->tableNames as $oldName => $newName) {
            $this->addSql("RENAME TABLE $newName TO $oldName");
        }
    }
}
