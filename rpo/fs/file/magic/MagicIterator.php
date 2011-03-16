<?php
/**
 * Manipulação do arquivo magic
 * @package	rpo.fs.file.magic
 * @author	João Batista Neto
 */

require_once 'rpo/fs/file/FileIterator.php';

/**
 * Implementação de um Iterator para arquivos magic
 */
class MagicIterator extends FilterIterator implements FileIterator {
	/**
	 * @var FileIterator
	 */
	private $iterator;

	/**
	 * Constroi o MagicIterator
	 * @param FileIterator $iterator
	 */
	public function __construct( FileIterator $iterator ) {
		parent::__construct( $iterator );

		$this->iterator = $iterator;
	}

	/**
	 * Define o objeto que será Iterado
	 * @param File $file
	 */
	public function setFileObject( File $file ) {
		$this->iterator->setFileObject( $file );
	}

	/**
	 * Define a posição do iterator
	 * @param integer $offset
	 */
	public function seek( $offset ) {
		$this->getInnerIterator()->seek( $offset );
	}

	/**
	 * Verifica se a linha atual do Iterator é válida. Comentáriso ou linhas vazias são descartadas
	 * @return boolean
	 */
	public function accept() {
		$current = $this->getInnerIterator()->current();

		return  !preg_match( '/^(\s*)(#.*|)$/' , $current );
	}
}