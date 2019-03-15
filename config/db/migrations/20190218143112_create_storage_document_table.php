<?php


use Phinx\Migration\AbstractMigration;

class CreateStorageDocumentTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('storage_document');
        $table
            ->addColumn('uuid', 'binary', ['null' => false, 'limit' => 16])
            ->addColumn('name', 'string', ['null' => false, 'limit' => 255])
            ->addColumn('tag', 'string', ['null' => true, 'limit' => 255])
            ->addColumn('status', 'integer', ['null' => false])
            ->addColumn('storage', 'string', ['null' => false])
            ->addColumn('path', 'string', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => ''])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->save();
    }
}
