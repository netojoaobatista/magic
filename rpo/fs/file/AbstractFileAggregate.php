<?php
/**
 * Objetos relacionados com manipulação de arquivos
 * @package	rpo.fs.file
 * @author	João Batista Neto
 */

require_once 'rpo/fs/file/File.php';
require_once 'rpo/fs/file/FileIterator.php';

/**
 * Implementação do Aggregate da interface File, essa classe tem como objetivo
 * reduzir a complexabilidade da implementação da interface File
 */
abstract class AbstractFileAggregate implements File {
	/**
	 * Nome da classe que será utilizada para construir o Iterator
	 * @var string
	 */
	private $iteratorClass;

	/**
	 * Constroi o Aggregate
	 * @param string $iteratorClass Classe que será utilizada para construir o Iterator
	 */
	public function __construct( $iteratorClass ) {
		$this->setIteratorClass( $iteratorClass );
	}

	/**
	 * Recupera o Iterator
	 * @return Iterator
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		$reflection = new ReflectionClass( $this->iteratorClass );
		$iterator = $reflection->newInstance();
		$iterator->setFileObject( $this );

		return $iterator;
	}

	/**
	 * Define a classe Iterator que será utilizada pelo método getIterator
	 * @param string $iteratorClass
	 * @throws InvalidArgumentException Se a classe especificada não implementar Iterator
	 * @throws RuntimeException Se a classe não for encontrada em tempo de execução
	 */
	public function setIteratorClass( $iteratorClass ) {
		if ( class_exists( $iteratorClass , false ) ) {
			if ( in_array( 'FileIterator' , class_implements( $iteratorClass , false ) ) ) {
				$this->iteratorClass = $iteratorClass;
			} else {
				throw new InvalidArgumentException( sprintf( '%s deve implementar FileIterator.' , $iteratorClass ) );
			}
		} else {
			throw new RuntimeException( sprintf( 'A classe %s não foi encontrada.' , $iteratorClass ) );
		}
	}
}