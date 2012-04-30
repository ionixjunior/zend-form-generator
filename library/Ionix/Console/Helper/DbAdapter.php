<?php

namespace Ionix\Console\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * Ionix Zend Form Generator Connection Helper.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Ione Souza Junior <junior@ionixjunior.com.br>
 */
class DbAdapter extends Helper
{
	
	/**
	 * Zend Db Table Adapter
	 * @var DbAdapter
	 */
	protected $_dbAdapter;
	
    /**
     * Constructor
     *
     * @param DbAdapter $dbAdapter 
     */
    public function __construct($dbAdapter)
    {
        $this->_dbAdapter = $dbAdapter;
    }

    /**
     * Retrieves Zend Db Table Adapter
     *
     * @return DbAdapter	
     */
    public function getDbAdapter()
    {
        return $this->_dbAdapter;
    }

    /**
     * @see Helper
     */
    public function getName()
    {
        return 'dbAdapter';
    }
}