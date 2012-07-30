<?php

namespace Ionix\Zend\Form\Generator\Db;

/**
 * Class responsible for performing the queries in PostgreSQL
 * 
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Ione Souza Junior <junior@ionixjunior.com.br>
 */
class Pgsql implements Behavior 
{
	/**
	 * @var Zend_Db_Adapter_Pdo_Pgsql
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
		$stmt = $this->getDb()
					 ->query("SELECT table_schema ".
					 		 "FROM information_schema.COLUMNS ".
					 		 "WHERE ( ".
					 		 "table_schema != 'information_schema' ".
					 		 "AND table_schema != 'pg_catalog' ".
					 		 ") ".
					 		 "AND table_catalog = :database ".
					 		 "GROUP BY table_schema ".
					 		 "ORDER BY table_schema", array(':database' => $database));
		$result = $stmt->fetchAll();
		
		$databaseSchemas = array();
		foreach( $result as $schema ){
			$databaseSchemas[] = $schema['table_schema'];
		}
		
		return $databaseSchemas;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getDatabaseTables()
	 */
	public function getDatabaseTables( $schema )
	{	
		$stmt = $this->getDb()
					 ->query("SELECT table_name ".
					 		 "FROM information_schema.COLUMNS ".
					 		 "WHERE  ".
					 		 "table_schema = :schema ".
					 		 "GROUP BY table_name ".
					 		 "ORDER BY table_name", array(':schema' => $schema));
		$result = $stmt->fetchAll();
		
		$databaseTables = array();
		foreach( $result as $table ){
			$databaseTables[] = $table['table_name'];
		}
		
		return $databaseTables;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getTableColumns()
	 */
	public function getTableColumns( $table, $schema = null )
	{	
		$stmt = $this->getDb()
				     ->query("SELECT *, ".
							 "(SELECT pg_catalog.obj_description(oid) FROM pg_catalog.pg_class c ".
							 "WHERE c.relname=cols.table_name) AS table_comment ".
							 ",(SELECT pg_catalog.col_description(oid,cols.ordinal_position::int) FROM pg_catalog.pg_class c where c.relname=cols.table_name) as column_comment ".
							 "FROM information_schema.columns cols ".
							 "WHERE table_name = :table AND table_schema = :schema", array(':table' => $table, ':schema' => $schema));
		$result = $stmt->fetchAll();
		
		$tableColumns = array();
		foreach( $result as $column ){
			
			$tableColumns[$column['column_name']] = array(
				'comment' 		=> $column['column_comment'],
				'type'			=> $this->getFieldType( $column['data_type'] ),
				'size'			=> $column['character_maximum_length'],
				'allowNull' 	=> $column['is_nullable'],
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
		$stmt = $this->getDb()
					 ->query("SELECT ".
							 "kcu.column_name, ".
							 "tc.table_name ".
							 "FROM ".
							 "information_schema.table_constraints AS tc ".
							 "JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name ".
							 "JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name ".
							 "WHERE constraint_type = 'PRIMARY KEY' ".
							 "AND tc.table_schema = :schema", array(':schema' => $schema));
		$result = $stmt->fetchAll();
		
		$tablePrimaryKeys = array();
		foreach( $result as $pk )
		{
			if( $table !== $pk['table_name'] )
			{
				continue;
			}
			$tablePrimaryKeys[] = $pk['column_name'];
		}
		
		return $tablePrimaryKeys;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getTableForeignKeys()
	 */
	public function getTableForeignKeys( $table, $schema = null )
	{	
		$stmt = $this->getDb()
					 ->query("SELECT ".
							 "kcu.column_name, ".
							 "tc.table_name ".
							 "FROM ".
							 "information_schema.table_constraints AS tc ".
							 "JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name ".
							 "JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name ".
							 "WHERE constraint_type = 'FOREIGN KEY' ".
							 "AND tc.table_schema = :schema", array(':schema' => $schema));
		$result = $stmt->fetchAll();
		
		$tableForeignKeys = array();
		foreach( $result as $fk ){
			if( $table !== $fk['table_name'] )
			{
				continue;
			}
			$tableForeignKeys[] = $fk['column_name'];
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
			case 'character varying':
			case 'character':
				$type = 'varchar';
				break;
			case 'integer':
				$type = 'integer';
				break;
		}
		
		return $type;	
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ionix\Zend\Form\Generator\Db.Behavior::getDb()
	 */
	public function getDb(){
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