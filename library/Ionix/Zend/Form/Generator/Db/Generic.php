<?php

namespace Ionix\Zend\Form\Generator\Db;

use Ionix\Zend\Form\Generator\Db;

/**
 * Generic class for handling database.
 * 
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Ione Souza Junior <junior@ionixjunior.com.br>
 */
class Generic 
{
	/**
	 * @var object
	 */
	private $adapter;
	
	/**
	 * @var array
	 */
	private $configuration;
		
	/**
	 * Constructor
	 * 
	 * @param object $adapter
	 */
	public function __construct( $adapter )
	{	
		$this->setConfiguration( $adapter->getConfig() );
		
		if ( $adapter instanceof \Zend_Db_Adapter_Pdo_Mysql ){
			$this->setAdapter( new Db\Mysql( $adapter ) );
		}
		
		if ( $adapter instanceof \Zend_Db_Adapter_Pdo_Pgsql ){
			$this->setAdapter( new Db\Pgsql( $adapter ) );
		}	
	}
	
	/**
	 * Returns information of the database.
	 * 
	 * @return array
	 */
	public function getDatabaseInformation()
	{	
		$databaseInformation = array();
		
		$configuration = $this->getConfiguration();
		$databaseSchemas = $this->getAdapter()->getDatabaseSchemas( $configuration['dbname'] );
		
		foreach( $databaseSchemas as $schema )
		{
			$databaseInformation[$schema] = array();
			$databaseTables = $this->getAdapter()->getDatabaseTables( $schema );
			
			foreach( $databaseTables as $table )
			{
				$databaseInformation[$schema][$table] = array();				
				
				$tableColumns = $this->getAdapter()->getTableColumns( $table, $schema );
				foreach( $tableColumns as $column )
				{
					$databaseInformation[$schema][$table] = $tableColumns;
				}
				
				$tablePrimaryKeys = $this->getAdapter()->getTablePrimaryKeys( $table, $schema );
				foreach( $tablePrimaryKeys as $pk )
				{					
					$databaseInformation[$schema][$table][$pk]['primaryKey'] = true;
				}
				
				$tableForeignKeys = $this->getAdapter()->getTableForeignKeys( $table, $schema );
				foreach( $tableForeignKeys as $fk )
				{
					$databaseInformation[$schema][$table][$fk]['foreignKey'] = true;
				}
			}
			
		}
		
		return $databaseInformation;	
	}
	
	/**
	 * @return the $adapter
	 */
	private function getAdapter()
	{
		return $this->adapter;
	}
	
	/**
	 * @return the $configuration
	 */
	private function getConfiguration()
	{
		return $this->configuration;
	}
	
	/**
	 * 
	 * @param object $adapter
	 */
	private function setAdapter( $adapter )
	{
		$this->adapter = $adapter;
	}
	
	/**
	 * @param array $configuration
	 */
	private function setConfiguration( $configuration )
	{
		$this->configuration = $configuration;
	}
}