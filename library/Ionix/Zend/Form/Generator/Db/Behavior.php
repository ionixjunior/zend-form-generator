<?php

namespace Ionix\Zend\Form\Generator\Db;

/**
 * Interface that contains the behavior that should have classes in the database adapter.
 * 
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Ione Souza Junior <junior@ionixjunior.com.br>
 */
interface Behavior 
{	
	/**
	 * Constructor
	 * 
	 * @param object $db
	 */
	public function __construct( $db );
	
	/**
	 * Returns the schemas of the database.
	 * 
	 * @param string $database
	 * @return array 
	 */
	public function getDatabaseSchemas( $database );
	
	/**
	 * Returns the tables of the database schema.
	 * 
	 * @param string $database
	 * @return array
	 */
	public function getDatabaseTables( $schema );
	
	/**
	 * Returns column information for a given table.
	 * 
	 * @param string $table
	 * @param string $schema
	 * @return array 
	 */
	public function getTableColumns( $table, $schema = null );
	
	/**
	 * Returns information regarding the primary keys of a given table.
	 * 
	 * @param string $table
	 * @param string $schema
	 * @return array 
	 */
	public function getTablePrimaryKeys( $table, $schema = null );
	
	/**
	 * Returns information regarding the foreign keys of a given table.
	 * 
	 * @param string $table
	 * @param string $schema
	 * @return array 
	 */
	public function getTableForeignKeys( $table, $schema = null );
	
	/**
	 * Returns the field type used.
	 * 
	 * @param string $type
	 * @return string
	 */
	public function getFieldType( $type );
	
	/**
	 * @return the $db
	 */
	public function getDb();
	
	/**
	 * 
	 * @param object $db
	 */
	public function setDb( $db );
	
}