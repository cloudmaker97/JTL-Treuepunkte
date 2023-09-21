<?php

namespace Plugin\dh_bonuspunkte\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Installation der Basis-Tabellen
 * @package Plugin\dh_bonuspunkte\Migrations
 */
class Migration20230922123000 extends Migration implements IMigration
{
    /**
     * @inheritDoc
     */
    protected $description = 'Installation der Basis-Tabellen';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `dh_bonus_history` ( `id` int(11) NOT NULL AUTO_INCREMENT, `text` longtext DEFAULT NULL, `points` int(11) NOT NULL, `userId` int(11) NOT NULL, `orderId` int(11) DEFAULT NULL, `createdAt` datetime DEFAULT NULL, `valuedAt` datetime DEFAULT NULL, PRIMARY KEY (`id`), KEY `userId` (`userId`))');
        $this->execute('CREATE TABLE IF NOT EXISTS `dh_bonus_last_rewarded` ( `id` int(11) NOT NULL AUTO_INCREMENT, `userId` int(11) DEFAULT NULL, `visitAt` datetime DEFAULT NULL, `loginAt` datetime DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS `dh_bonus_history`');
        $this->execute('DROP TABLE IF EXISTS `dh_bonus_last_rewarded`');
    }
}