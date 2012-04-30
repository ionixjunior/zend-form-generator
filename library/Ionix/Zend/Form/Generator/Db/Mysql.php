<?php

namespace Ionix\Zend\Form\Generator\Db;

/**
 * Class responsible for performing the queries in MySQL
 * 
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Ione Souza Junior <junior@ionixjunior.com.br>
 */
class Mysql implements Behavior 
{	
	/**
	 * @var Zend_Db_Adapter_Pdo_Mysql
	 */
	private $db;
	
	/**
	 * Constructor
	 * 
	 * @param object $db
	 */
	public function __construct( $db )
	{
		$this->setDb( $db );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getDatabaseSchemas()
	 */
	public function getDatabaseSchemas( $database )
	{
		return array( $database );
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getDatabaseTables()
	 */
	public function getDatabaseTables( $schema )
	{	
		$stmt = $this->getDb()->query("SHOW TABLES FROM $schema");
		$result = $stmt->fetchAll();
		
		$databaseTables = array();
		foreach( $result as $table ){
			$databaseTables[] = $table['Tables_in_' . $schema];
		}
		
		return $databaseTables;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getTableColumns()
	 */
	public function getTableColumns( $table, $schema = null )
	{	
		$stmt = $this->getDb()->query("SELECT COLUMN_NAME, COLUMN_COMMENT, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE FROM information_schema.columns WHERE table_schema = :schema AND table_name = :table", array(':schema' => $schema, ':table' => $table));
		$result = $stmt->fetchAll();
		
		$tableColumns = array();
		foreach( $result as $column ){
			
			$tableColumns[$column['COLUMN_NAME']] = array(
				'comment' 		=> $column['COLUMN_COMMENT'],
				'type'			=> $this->getFieldType( $column['DATA_TYPE'] ),
				'size'			=> $column['CHARACTER_MAXIMUM_LENGTH'],
				'allowNull' 	=> $column['IS_NULLABLE'],
				'primaryKey'	=> false,
				'foreignKey'	=> false
			);
			
		}
		
		return $tableColumns;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getTablePrimaryKeys()
	 */
	public function getTablePrimaryKeys( $table, $schema = null )
	{	
		$stmt = $this->getDb()->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = :schema AND table_name = :table AND COLUMN_KEY = 'PRI'", array(':schema' => $schema, ':table' => $table));
		$result = $stmt->fetchAll();
		
		$tablePrimaryKeys = array();
		foreach( $result as $pk ){
			$tablePrimaryKeys[] = $pk['COLUMN_NAME'];
		}
		
		return $tablePrimaryKeys;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getTableForeignKeys()
	 */
	public function getTableForeignKeys( $table, $schema = null )
	{	
		$stmt = $this->getDb()->query("SELECT COLUMN_NAME FROM information_schema.key_column_usage WHERE referenced_table_name IS NOT NULL AND table_schema = :schema AND table_name = :table", array(':schema' => $schema, ':table' => $table));
		$result = $stmt->fetchAll();
		
		$tableForeignKeys = array();
		foreach( $result as $fk ){
			$tableForeignKeys[] = $fk['COLUMN_NAME'];
		}
		
		return $tableForeignKeys;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getFieldType()
	 */
	public function getFieldType( $type )
	{	
		switch( $type ){
			case 'char':
			case 'varchar':
				$type = 'varchar';
				break;
			case 'int':
				$type = 'integer';
				break;
		}
		
		return $type;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getDb()
	 */
	public function getDb()
	{
		return $this->db;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::setDb()
	 */
	public function setDb( $db )
	{
		$this->db = $db;
	}
}