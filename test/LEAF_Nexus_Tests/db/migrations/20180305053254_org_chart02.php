<?php


use Phinx\Migration\AbstractMigration;

class OrgChart02 extends AbstractMigration
{
    public function up()
    {
        $migrationContents = file_get_contents('../../LEAF_Nexus/db_upgrade/Update_OC_DB_2737-2751.sql');
    }

    public function down()
    {
    }
}
