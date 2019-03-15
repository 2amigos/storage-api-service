<?php


use Phinx\Migration\AbstractMigration;

class CreateStorageDocumentStatusTable extends AbstractMigration
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
        $table = $this->table('storage_document_status');
        $table
            ->addColumn('document_id', 'integer', ['null' => false])
            ->addColumn('status', 'integer', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => ''])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('document_id', 'document', 'id', array('delete'=>'CASCADE', 'update'=>'CASCADE'))
            ->save();
    }
}
