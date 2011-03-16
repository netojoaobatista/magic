<?php
/**
 * Objetos relacionados com manipulação de arquivos
 * @package	rpo.fs.file
 * @author	João Batista Neto
 */

/**
 * Interface para um iterator de arquivos
 */
interface FileIterator extends SeekableIterator {
	/**
	 * Define o objeto que será iterado
	 * @param File $file
	 */
	public function setFileObject( File $file );
}