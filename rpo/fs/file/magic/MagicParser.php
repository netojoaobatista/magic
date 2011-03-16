<?php
/**
 * Manipulação do arquivo magic
 * @package	rpo.fs.file.magic
 * @author	João Batista Neto
 */

require_once 'rpo/fs/file/File.php';
require_once 'rpo/fs/file/magic/MimeMagicFile.php';

/**
 * Implementação de um parser para o arquivo magic
 */
class MagicParser {
	/**
	 * @var MimeMagicFile
	 */
	private $magic;

	/**
	 * @var File
	 */
	private $file;

	/**
	 * Constroi o parser de linhas do arquivo magic
	 * @param File $file
	 * @param MimeMagicFile $magic
	 */
	public function __construct( File $file , MimeMagicFile $magic ) {
		$this->magic = $magic;
		$this->file = $file;
	}

	/**
	 * Interpreta a linha do arquivo magic e compara com o arquivo passado para descobrir
	 * o Content-Type do arquivo
	 * @param Iterator $iterator
	 */
	public function parse( Iterator $iterator ) {
		$ret = false;
		$match = array();
		$line = preg_replace( array( "/\r\n|\r|\n/" , '/\s{2,}/' ) , array( null , "\t" ) , $iterator->current() );

		if ( preg_match( "/^(?<d>\\>)?(?<b>\\d+)\\s*(?<t>(byte|string|(be|le)?(short|long|date)))\\s+(?<c>[^\t]+)(\\s*(?<mt>[^\t]+)(\\s*(?<me>[^\t]+))?)?$/s" , $line , $match ) ) {
			$byteNumber = (int) $match[ 'b' ];

			switch ( $match[ 't' ] ) {
				case 'string' :
					$match[ 'c' ] = stripcslashes( $match[ 'c' ] );
				case 'byte' :
					$dataContents = $match[ 'c' ];
					break;
				case 'short' :
					$dataContents = pack( 'S' , $this->numberConvert( $match[ 'c' ] ) );
					break;
				case 'beshort' :
					$dataContents = pack( 'n' , $this->numberConvert( $match[ 'c' ] ) );
					break;
				case 'leshort' :
					$dataContents = pack( 'v' , $this->numberConvert( $match[ 'c' ] ) );
					break;
				case 'long' :
					$dataContents = pack( 'L' , $this->numberConvert( $match[ 'c' ] ) );
					break;
				case 'belong' :
					$dataContents = pack( 'N' , $this->numberConvert( $match[ 'c' ] ) );
					break;
				case 'lelong' :
					$dataContents = pack( 'V' , $this->numberConvert( $match[ 'c' ] ) );
					break;

				// TODO: implementar os tipos date, ledate e bedate
				case 'date' :
				case 'ledate' :
				case 'bedate' :
				default :
					return false;
			}

			if ( $this->compare( $byteNumber , $dataContents ) ) {
				$ret = $match[ 'mt' ];

				if (  !empty( $match[ 'me' ] ) ) {
					$ret = sprintf( '%s; charset=%s' , $ret , $match[ 'me' ] );
				}

				if ( empty( $match[ 'd' ] ) ) {
					$offset = $this->magic->tell();
					$magic = new MagicParser( $this->file , $this->magic );

					do {
						$iterator->next();
						$current = $iterator->current();

						if ( substr( $current , 1 , 1 ) != '>' ) {
							$this->magic->seek( $offset );
							break;
						} else {
							$offset = $this->magic->tell();

							if ( ( $newRet = $magic->parse( $iterator ) ) !== false ) {
								$ret = & $newRet;

								for (; $iterator->valid() ; $iterator->next() ) {
									if ( substr( $iterator->current() , 1 , 1 ) == '>' ) {
										$this->magic->seek( $offset );
										break 2;
									} else {
										$offset = $this->magic->tell();
									}
								}
							}
						}
					} while ( $iterator->valid() && ( substr( $current , 1 , 1 ) == '>' ) );
				}
			}
		}

		return $ret;
	}

	/**
	 * Faz a comparação com o arquivo
	 * @param integer $byteNumber Número do byte que deverá ser iniciada a verificação
	 * @param string $dataContents Conteúdo que deverá ser comparado
	 * @return boolean
	 */
	private function compare( $byteNumber , $dataContents ) {
		$ret = false;

		$this->file->seek( $byteNumber );

		if (  !$this->file->eof() ) {
			$read = $this->file->read( strlen( $dataContents ) );

			return $read == $dataContents;
		}

		return $ret;
	}

	/**
	 * Converte um valor numérico para seu inteiro equivalente
	 * @param string $dataContents
	 * @return integer
	 */
	private function numberConvert( $dataContents ) {
		$val = null;

		if ( preg_match( '/^[1-9][0-9]*$/' , $dataContents ) ) {
			$val = (int) $dataContents;
		} elseif ( preg_match( '/^0[0-7]+$/' , $dataContents ) ) {
			$val = base_convert( $dataContents , 8 , 10 );
		} elseif ( preg_match( '/0x[a-f0-9]+/i' , $dataContents ) ) {
			$val = base_convert( $dataContents , 16 , 10 );
		} else {
			throw new UnexpectedValueException( sprintf( 'Formato de número desconhecido: %s' , $dataContents ) );
		}

		return $val;
	}
}