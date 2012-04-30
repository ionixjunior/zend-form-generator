<?php

namespace Ionix\Zend\Form\Generator\File;

/**
 * Class responsible for generate form files from database.
 * 
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Ione Souza Junior <junior@ionixjunior.com.br>
 */
class Creator 
{
	/**
	 * @var string
	 */
	private $namespace = null;
	
	/**
	 * @var string
	 */
	private $classPrefix = 'Application_Form_';
	
	/**
	 * @var string
	 */
	private $fileDestination;
	
	/**
	 * @var string
	 */
	private $fileExtension = '.php';
	
	/**
	 * @var boolean
	 */
	private $generatePrimaryKeys = false;
	
	/**
	 * @var string
	 */
	private $namespaceCharacter = '\\';
	
	/**
	 * @var array
	 */
	private $constructorParameters;
	
	/**
	 * Method for generate the file form.
	 * 
	 * @param string $table
	 * @param string $schema
	 * @param array $column
	 * @return string
	 */
	public function generateFile( $table, $schema, $columns )
	{	
		try {
			
			$className = $fileName = $formName = $this->adjustsName($table);
			if( is_null($this->getNamespace()) )
			{
				$className = $this->getClassPrefix() . $className;
			}
			
			$extendedClass = 'Zend_Form';
			if( !is_null($this->getNamespace()) )
			{
				$extendedClass = $this->getNamespaceCharacter() . $extendedClass;
			}
			
			$class = new \Zend_CodeGenerator_Php_Class();
			$class->setExtendedClass($extendedClass);
			$class->setName( $className );
			$class->setDocblock(
			    new \Zend_CodeGenerator_Php_Docblock(array(
				    'shortDescription' => $className . ' form file.'
				))
			);
			
			$constructBody  = 'parent::__construct($options);' . PHP_EOL . PHP_EOL;
			$constructBody .= '$this->setName(\'frm' . $formName . '\');' . PHP_EOL;
			$constructBody .= '$this->setMethod(\'post\');' . PHP_EOL . PHP_EOL;
			
			$this->resetConstructParameters();
			foreach($columns as $columnName => $params)
			{
				if( $params['primaryKey'] === true && $this->getGeneratePrimaryKeys() === false )
				{
					continue;
				}
				
				$elementName = $this->adjustsName($columnName, true);
				$constructBody .= $this->createContentOfElementColumn($elementName, $columnName, $params);
			}

			$constructBody .= $this->createContentSubmit();
			$this->addConstructorParameters( array('name' => 'options', 'defaultValue' => null) );
			
			$class->setMethods(array(
			    array(
			        'name'       => '__construct',
			        'parameters' => $this->getConstructorParameters(),
			        'body'       => $constructBody
			    )
			));
			
			$code = $class->generate();
			if( !is_null($this->getNamespace()) )
			{
				$code = 'namespace ' . $this->getNamespace() . ';' . PHP_EOL . PHP_EOL . $code;
			}
			$code = '<?php' . PHP_EOL . PHP_EOL . $code;
			
			file_put_contents($this->getFileDestination() . '/' . $fileName . $this->getFileExtension(), $code);
			return 'Generated file ' . $fileName . $this->getFileExtension() . '...' . PHP_EOL;
			
		} catch ( Exception $e ){
			return $e->getMessage();
		}	
	}
	
	/**
	 * Creates the contents of a column table element.
	 * 
	 * @param string $elementVariable
	 * @param string $columnName
	 * @param array $columnParams
	 * @return string
	 */
	private function createContentOfElementColumn($elementVariable, $columnName, $columnParams)
	{
		$content = '';
		if( $columnParams['foreignKey'] === true )
		{
			$content .= '$' . $elementVariable . ' = new ';
			if( !is_null($this->getNamespace()) )
			{
				$content .= $this->getNamespaceCharacter();
			}
			$content .= 'Zend_Form_Element_Select(\'' . $columnName . '\');' . PHP_EOL;
			
			$content .= $this->createContentOfLabel($elementVariable, $columnParams);
			$content .= $this->createContentOfAttributes($elementVariable, $columnParams);
			$content .= $this->createContentOfValidators($elementVariable, $columnParams);
			
			$content .= '$' . $elementVariable . '->addMultiOptions($data' . $this->adjustsName($columnName) . ');' . PHP_EOL;
			$content .= '$this->addElement($' . $elementVariable . ');' . PHP_EOL . PHP_EOL;
			
			$this->addConstructorParameters( array('type' => 'Array', 'name' => 'data' . $this->adjustsName($columnName)) );
			return $content;
		}
		
		$content .= '$' . $elementVariable . ' = new ';
		if( !is_null($this->getNamespace()) )
		{
			$content .= $this->getNamespaceCharacter();
		}
		switch($columnParams['type'])
		{
			case 'varchar':
				$content .= 'Zend_Form_Element_Text(\''. $columnName . '\');' . PHP_EOL;
				break;
			case 'text':
				$content .= 'Zend_Form_Element_Textarea(\'' . $columnName . '\');' . PHP_EOL;
				break;
			default:
				$content .= 'Zend_Form_Element_Text(\'' . $columnName . '\');' . PHP_EOL;
				break;
		}
		
		$content .= $this->createContentOfLabel($elementVariable, $columnParams);
		$content .= $this->createContentOfAttributes($elementVariable, $columnParams);
		$content .= $this->createContentOfValidators($elementVariable, $columnParams);
		$content .= '$this->addElement($' . $elementVariable . ');' . PHP_EOL . PHP_EOL;
		
		return $content;
	}
	
	/**
	 * Creates the contents of a validator column table element.
	 * 
	 * @param string $elementVariable
	 * @param array $columnParams
	 * @return string
	 */
    private function createContentOfValidators($elementVariable, $columnParams)
	{
		$content = '';
		if( $columnParams['allowNull'] === 'NO' )
		{
			$content .= '$' . $elementVariable . '->setRequired(true);' . PHP_EOL;
			$content .= '$' . $elementVariable . '->addValidator(new ';
			if( !is_null($this->getNamespace()) )
			{
				$content .= $this->getNamespaceCharacter();
			}
			$content .= 'Zend_Validate_NotEmpty()';
			$content .= ');' . PHP_EOL;
		}
		
		switch( $columnParams['type'] )
		{
			case 'integer':
				$content .= '$' . $elementVariable . '->addValidator(new ';
				if( !is_null($this->getNamespace()) )
				{
					$content .= $this->getNamespaceCharacter();
				}
				$content .= 'Zend_Validate_Int()';
				$content .= ');' . PHP_EOL;
				break;
		}
		
		return $content;
	}
	
	/**
	 * Creates the contents of a attributes column table element.
	 * 
	 * @param string $elementVariable
	 * @param array $columnParams
	 * @return string
	 */
	private function createContentOfAttributes($elementVariable, $columnParams)
	{
		$content = '';
		
		if( !is_null($columnParams['size']) )
		{
			$content .= '$' . $elementVariable . '->setAttrib(\'maxlength\', ' . $columnParams['size'] . ');' . PHP_EOL;
		}
		
		return $content;
	}
	
	/**
	 * Creates the contents of a label column table element.
	 * 
	 * @param string $elementVariable
	 * @param array $columnParams
	 * @return string
	 */
	private function createContentOfLabel($elementsVariable, $columnParams)
	{
		$content = '';
		$content .= '$' . $elementsVariable . '->setLabel(\'';
		$content .= is_null($columnParams['comment']) ? $elementsVariable : $columnParams['comment'];
		$content .= '\');' . PHP_EOL;
		return $content;
	}
	
	/**
	 * Creates the contents of a submit button.
	 * 
	 * @return string
	 */
	private function createContentSubmit()
	{
		$content = '';
		$content .= '$submit = new ';
		if( !is_null($this->getNamespace()) )
		{
			$content .= $this->getNamespaceCharacter();
		}
		$content .= 'Zend_Form_Element_Submit(\'bt_submit\');' . PHP_EOL;
		$content .= '$submit->setLabel(\'Save\');' . PHP_EOL;
		$content .= '$this->addElement($submit);';
		return $content;
	}
	
	/**
	 * Adjust the name.
	 * 
	 * @param string $name
	 * @param boolean $lcfirst
	 * @return string
	 */
	private function adjustsName($name, $lcfirst = false)
	{
		$name    = strtolower($name);
		$name    = explode('_', $name);
		$newName = '';
		foreach( $name as $value )
		{
			$newName .= ucfirst($value);
		}
		
		if( $lcfirst === true )
		{
			$newName = lcfirst($newName);
		}
		return $newName;
	}
	
	/**
	 * @return the $namespace
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @return the $classPrefix
	 */
	public function getClassPrefix()
	{
		return $this->classPrefix;
	}

	/**
	 * @return the $fileDestination
	 */
	public function getFileDestination()
	{
		return $this->fileDestination;
	}

	/**
	 * @return the $fileExtension
	 */
	public function getFileExtension()
	{
		return $this->fileExtension;
	}

	/**
	 * @return the $generatePrimaryKeys
	 */
	public function getGeneratePrimaryKeys()
	{
		return $this->generatePrimaryKeys;
	}
	
	/**
	 * @return the $namespaceCharacter
	 */
	public function getNamespaceCharacter()
	{
		return $this->namespaceCharacter;
	}
	
	/**
	 * @return the $constructorParameters
	 */
	public function getConstructorParameters()
	{
		return $this->constructorParameters;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = str_replace('\\', '\\', $namespace);
	}

	/**
	 * @param string $classPrefix
	 */
	public function setClassPrefix($classPrefix)
	{
		$this->classPrefix = $classPrefix;
	}

	/**
	 * @param string $fileDestination
	 */
	public function setFileDestination($fileDestination)
	{
		$this->fileDestination = $fileDestination;
	}

	/**
	 * @param string $fileExtension
	 */
	public function setFileExtension($fileExtension)
	{
		$this->fileExtension = $fileExtension;
	}

	/**
	 * @param boolean $generatePrimaryKeys
	 */
	public function setGeneratePrimaryKeys($generatePrimaryKeys)
	{
		$this->generatePrimaryKeys = $generatePrimaryKeys;
	}
	
	/**
	 * @param string $namespaceCharacter
	 */
	public function setNamespaceCharacter($namespaceCharacter)
	{
		$this->namespaceCharacter = $namespaceCharacter;
	}
	
    /**
	 * @param array $constructorParameters
	 */
	private function addConstructorParameters($constructorParameters)
	{
	    $this->constructorParameters[] = $constructorParameters;
	}
	
	/**
	 * Reset all construct parameters exists.
	 */
	private function resetConstructParameters()
	{
		$this->constructorParameters = array();
	}
}