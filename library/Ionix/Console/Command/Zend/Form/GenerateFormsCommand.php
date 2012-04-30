<?php

namespace Ionix\Console\Command\Zend\Form;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

/**
 * Command to generate form classes.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Ione Souza Junior <junior@ionixjunior.com.br>
 */
class GenerateFormsCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('generate-forms')
        ->setDescription('Generate form classes from your database information.')
        ->setDefinition(array(
            new InputArgument(
                'dest-path', 
                InputArgument::REQUIRED, 
                'The path to generate your forms classes.'
            ),
            new InputArgument(
                'namespace', 
                InputArgument::OPTIONAL,
                'The namespace name where the forms will be generated. If you do not want to use namespace, the forms are created in standard Application_Form_NAMEFORM.',
                0
            ),
            new InputOption(
                'primary-keys', 
                null, 
                InputOption::VALUE_NONE,
                'Defines the primary keys to be inserted on the forms.'
            )
        ))
        ->setHelp(<<<EOT
Generate form classes from your database information.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $destPath = realpath($input->getArgument('dest-path'));
		
        if ( ! file_exists($destPath) )
        {
            throw new \InvalidArgumentException(
                sprintf('Forms destination directory "%s" does not exist.', $destPath)
            );
        } 
        
        if ( ! is_writable($destPath) )
        {
            throw new \InvalidArgumentException(
                sprintf('Forms destination directory "%s" does not have write permissions.', $destPath)
            );
        }
		
		$output->write('Loading information from the database...' . PHP_EOL);
			
		$generic = new \Ionix\Zend\Form\Generator\Db\Generic($this->getHelper('dbAdapter')->getDbAdapter());
		$databaseInformation = $generic->getDatabaseInformation();
		
		$file = new \Ionix\Zend\Form\Generator\File\Creator();
		$file->setFileDestination($destPath);
		
		$namespace = $input->getArgument('namespace');
		if( !empty($namespace) )
		{
			$file->setNamespace( $namespace );
		}
		if( $input->getOption('primary-keys') === true )
		{
			$file->setGeneratePrimaryKeys(true);
		}

		foreach( $databaseInformation as $schema => $tables )
		{
			foreach( $tables as $tableName => $columnsInformation )
			{
				$result = $file->generateFile( $tableName, $schema, $columnsInformation );
				$output->write($result);
			}
		}
		
		$output->write('Generation process forms completed successfully!');
    }
}
