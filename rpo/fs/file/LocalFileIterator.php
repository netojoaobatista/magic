<?php
/**
 * Objetos relacionados com manipulação de arquivos
 * @package	rpo.fs.file
 * @author	João Batista Neto
 */

require_once 'FileIterator.php';

/**
 * Implementação de um Iterator para um arquivo local
 */
class LocalFileIterator implements FileIterator {
	/**
	 * Linha atual
	 * @var string
	 */
	private $current;

	/**
	 * Arquivo que será Iterado
	 * @var File
	 */
	private $file;

	/**
	 * Define o objeto File que será iterado
	 * @param File $file
	 * @see FileIterator::setFileObject()
	 */
	public function setFileObject( File $file ) {
		$this->file = $file;
		$this->rewind();
	}

	/**
	 * Recupera a linha atual do arquivo
	 * @return string
	 * @see Iterator::current()
	 */
	public function current() {
		return $this->current;
	}

	/**
	 * Recupera a posição atual do ponteiro de arquivo
	 * @return integer
	 * @see Iterator::key()
	 */
	public function key() {
		if ( $this->testFile() ) {
			return $this->file->tell();
		}
	}

	/**
	 * Avança o ponteiro de arquivo à próxima linha
	 * @see Iterator::next()
	 */
	public function next() {
		if ( $this->testFile() ) {
			try {
				$this->current = $this->file->readLine();
			} catch ( Exception $e ) {
				$this->current = null;
			}
		}
	}

	/**
	 * Reinicia o Iterator
	 * @see Iterator::rewind()
	 */
	public function rewind() {
		if ( $this->testFile() ) {
			$this->file->seek( 0 );
			$this->current = $this->file->readLine();
		}
	}

	/**
	 * Posiciona o ponteiro de arquivos em um local específico
	 * @param integer $offset
	 * @see SeekableIterator::seek()
	 */
	public function seek( $offset ) {
		if ( $this->testFile() ) {
			$this->file->seek( $offset );
		}
	}

	/**
	 * Verifica se o Iterator é válido
	 * @return boolean
	 * @see Iterator::valid()
	 */
	public function valid() {
		if ( $this->testFile() ) {
			return  !is_null( $this->current ) && ( $this->current !== false ) &&  !$this->file->eof();
		}
	}

	/**
	 * Verifica se o objeto File foi definido
	 * @return boolean
	 * @throws RuntimeException Se o objeto não tiver sido definido
	 */
	private function testFile() {
		if ( $this->file instanceof File ) {
			return true;
		} else {
			throw new RuntimeException( 'FileObject não definido.' );
		}
	}
}