<?php
/**
 * Manipulação do arquivo magic
 * @package	rpo.fs.file.magic
 * @author	João Batista Neto
 */

require_once 'rpo/fs/file/LocalFile.php';
require_once 'rpo/fs/file/magic/MagicIterator.php';
require_once 'rpo/fs/file/magic/MagicParser.php';

/**
 * Manipulador do arquivo magic
 */
class MimeMagicFile extends LocalFile {
	/**
	 * Constroi o objeto do manipulador do arquivo MimeMagicFile
	 * @param string $path Diretório que contém o arquivo mágic
	 */
	public function __construct( $path = '.' ) {
		$path = sprintf( '%s/magic' , $path );

		if ( !is_file( $path ) ) {
			$path = sprintf( '%s/magic' , __DIR__ );
		}

		parent::__construct( $path );

		$this->open( 'r' );
	}

	/**
	 * Recupera o Iterator
	 * @return Iterator
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new MagicIterator( parent::getIterator() );
	}

	/**
	 * Recupera o Mime-type de um arquivo
	 * @param File $file
	 * @return string
	 */
	public function getMimeType( File $file ) {
		$ret = false;
		$iterator = $this->getIterator();
		$parser = new MagicParser( $file , $this );

		if (  !$file->isOpened() ) $file->open( 'r' );

		for ( $iterator->rewind() ; $iterator->valid() ; $iterator->next() ) {
			if ( ( $ret = $parser->parse( $iterator ) ) !== false ) {
				break;
			}
		}

		return $ret;
	}
}