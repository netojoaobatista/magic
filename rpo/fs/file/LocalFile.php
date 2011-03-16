<?php
/**
 * Objetos relacionados com manipulação de arquivos
 * @package	rpo.fs.file
 * @author	João Batista Neto
 */

require_once 'rpo/fs/file/AbstractFileAggregate.php';
require_once 'LocalFileIterator.php';

/**
 * Implementação da interface File para um arquivo local
 */
class LocalFile extends AbstractFileAggregate {
	/**
	 * Manipulador de arquivo
	 * @var resource
	 */
	private $handler;

	/**
	 * Nome do arquivo
	 * @var string
	 */
	private $file;

	/**
	 * Caminho do arquivo
	 * @var string
	 */
	private $path;

	/**
	 * Constroi o objeto de arquivo local
	 * @param string $file Nome do arquivo que será aberto
	 */
	public function __construct( $file ) {
		parent::__construct( 'LocalFileIterator' );

		$this->file = $file;
		$this->path = dirname( $file );
	}

	/**
	 * Destroi o objeto e fecha o arquivo, se estiver aberto
	 */
	public function __destruct() {
		if ( $this->isOpened() ) {
			$this->close();
		}
	}

	/**
	 * Fecha o arquivo
	 * @return boolean
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function close() {
		try {
			if ( $this->testResource() ) {
				$close = fclose( $this->handler );

				$this->file = null;
				$this->handler = null;
				$this->mode = null;

				return $close;
			}
		} catch ( RuntimeException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}
	}

	/**
	 * Verifica se o manipulador chegou ao fim do arquivo
	 * @return boolean
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function eof() {
		try {
			if ( $this->testResource() ) {
				return feof( $this->handler );
			}
		} catch ( RuntimeException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}
	}

	/**
	 * Verifica se o arquivo foi aberto
	 * @return boolean
	 */
	public function isOpened() {
		return $this->testResource( false );
	}

	/**
	 * Verifica se o arquivo possui permissões de leitura
	 * @return boolean
	 */
	public function isReadable() {
		if ( is_file( $this->file ) ) {
			return is_readable( $this->file );
		} else {
			throw new RuntimeException( sprintf( 'O arquivo "%s" não foi encontrado.' , $this->file ) );
		}
	}

	/**
	 * Verifica se o arquivo possui permissões de gravação
	 * @return boolean
	 */
	public function isWritable() {
		return is_writable( $this->file );
	}

	/**
	 * Abre o arquivo
	 * @param string $mode Modo de abertura do arquivo
	 * @param boolean $useIncludePath Define se deverá ser utilizado o include_path caso
	 * o arquivo não seja localizado
	 * @return boolean
	 */
	public function open( $mode = 'r' , $useIncludePath = false ) {
		if ( $this->isOpened() ) {
			$this->close();
		}

		switch ( $mode ) {
			case 'r' :
			case 'r+' :
				if (  !$this->isReadable() ) {
					throw new RuntimeException( sprintf( 'Não temos permissões de leitura no arquivo "%s".' , $this->file ) );
				}
				break;
			case 'a+' :
			case 'w+' :
				$exists = is_file( $this->file );

				if ( $exists &&  !$this->isReadable() ) {
					throw new RuntimeException( sprintf( 'Não temos permissões de leitura no arquivo "%s".' , $this->file ) );
				}
			case 'a' :
			case 'w' :
				if ( $exists &&  !$this->isWritable() ) {
					throw new RuntimeException( sprintf( 'Não temos permissões de gravação no arquivo "%s".' , $this->file ) );
				} elseif (  !$exists &&  !is_writable( $this->path ) ) {
					throw new RuntimeException( sprintf( 'Não temos permissões de gravação no arquivo "%s".' , $this->file ) );
				}
		}

		if ( ( $this->handler = fopen( $this->file , $mode , (bool) $useIncludePath ) ) === false ) {
			throw new RuntimeException( sprintf( 'Não foi possível abrir o arquivo "%s".' , $this->file ) );
		}

		return true;
	}

	/**
	 * Lê uma porção de bytes do arquivo
	 * @param integer $length Quantidade de bytes que serão lidos
	 * @return string
	 * @throws RuntimeException Se não for possível ler a partir do arquivo
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function read( $length ) {
		try {
			if ( $this->testResource() ) {
				$read = fread( $this->handler , $length );

				if ( $read === false ) {
					throw new RuntimeException( 'Não foi possível ler a partir do arquivo.' );
				}

				return $read;
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}
	}

	/**
	 * Lê uma linha do arquivo
	 * @param integer $length Quantidade de bytes que serão lidos
	 * @return string
	 * @throws RuntimeException Se não for possível ler a partir do arquivo
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function readLine( $length = 1024 ) {
		try {
			if ( $this->testResource() ) {
				$read = fgets( $this->handler , $length );

				if ( $read === false ) {
					throw new RuntimeException( 'Não foi possível ler a partir do arquivo.' );
				}

				return $read;
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}
	}

	/**
	 * Posiciona o ponteiro de arquivo em um local específico
	 * @param integer $offset Posição que o ponteiro será colocado
	 * @param integer $mode Modo de posicionamento
	 * @return boolean
	 * @throws RuntimeException Se não for possível posicionar o ponteiro de arquivos
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function seek( $offset , $mode = 0 ) {
		try {
			if ( $this->testResource() ) {
				if ( fseek( $this->handler , $offset , $mode ) ==  -1 ) {
					throw new RuntimeException( 'Não foi possível posicionar o ponteiro de arquivo.' );
				}
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}

		return true;
	}

	/**
	 * Recupera a posição atual do ponteiro de arquivo
	 * @return integer
	 * @throws RuntimeException Se não for possível recuperar a posição do ponteiro de arquivos
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function tell() {
		try {
			if ( $this->testResource() ) {
				$tell = ftell( $this->handler );

				if ( $tell === false ) {
					throw new RuntimeException( 'Não foi possível posicionar o ponteiro de arquivo.' );
				}

				return $tell;
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}
	}

	/**
	 * Verifica se o recurso de arquivo é válido
	 * @return boolean
	 * @throws LogicException Se o recurso não existir e $throws for definido como TRUE
	 */
	private function testResource( $throws = true ) {
		if ( is_resource( $this->handler ) ) {
			return true;
		} elseif ( $throws ) {
			throw new LogicException( 'O arquivo não foi aberto.' );
		}

		return false;
	}

	/**
	 * Diminui o tamanho do arquivo para um número específico
	 * @param integer $size O novo tamanho do arquivo
	 * @return boolean
	 * @throws RuntimeException Se não for possível modificar o tamanho do arquivo
	 * @throws BadMethodCallException Se o arquivo não tiver sido aberto
	 */
	public function truncate( $size = 0 ) {
		try {
			if ( $this->testResource() ) {
				if (  !ftruncate( $this->handler , $size ) ) {
					throw new RuntimeException( 'Não foi possível modificar o tamanho do arquivo.' );
				}
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}

		return true;
	}

	/**
	 * Escreve uma porção de bytes no arquivo
	 * @param string $data Os dados que serão gravados
	 * @return boolean
	 */
	public function write( $data ) {
		try {
			if ( $this->testResource() ) {
				if ( fwrite( $this->handler , $data , strlen( $data ) ) === false ) {
					throw new RuntimeException( 'Não foi possível gravar no arquivo.' );
				}
			}
		} catch ( LogicException $e ) {
			throw new BadMethodCallException( sprintf( 'O arquivo precisa ser aberto antes de se utilizar %s::%s' , get_class( $this ) , __METHOD__ ) , $e->getCode() , $e );
		}

		return true;
	}
}