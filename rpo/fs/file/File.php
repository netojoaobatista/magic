<?php
/**
 * Objetos relacionados com manipulação de arquivos
 * @package	rpo.fs.file
 * @author	João Batista Neto
 */

/**
 * Interface para definição de um arquivo
 */
interface File extends IteratorAggregate {
	/**
	 * Fecha o arquivo
	 * @return boolean
	 */
	public function close();

	/**
	 * Verifica se o ponteiro do arquivo chegou no final
	 * @return boolean
	 */
	public function eof();

	/**
	 * Verifica se o arquivo foi aberto
	 * @return boolean
	 */
	public function isOpened();

	/**
	 * Verifica se o arquivo possui permissões de leitura
	 * @return boolean
	 */
	public function isReadable();

	/**
	 * Verifica se o arquivo possui permissões de gravação
	 * @return boolean
	 */
	public function isWritable();

	/**
	 * Abre o arquivo
	 * @param string $mode Modo de abertura do arquivo
	 * @return boolean
	 */
	public function open( $mode = 'r' );

	/**
	 * Lê uma porção de bytes do arquivo
	 * @param integer $length Quantidade de bytes que serão lidos
	 * @return string
	 */
	public function read( $length );

	/**
	 * Lê uma linha do arquivo
	 * @param integer $length Quantidade de bytes que serão lidos
	 * @return string
	 */
	public function readLine( $length = 1024 );

	/**
	 * Posiciona o ponteiro de arquivo em um local específico
	 * @param integer $offset Posição que o ponteiro será colocado
	 * @param integer $mode Modo de posicionamento
	 * @return boolean
	 */
	public function seek( $offset , $mode = 0 );

	/**
	 * Recupera a posição atual do ponteiro de arquivo
	 * @return integer
	 */
	public function tell();

	/**
	 * Diminui o tamanho do arquivo para um número específico
	 * @param integer $size O novo tamanho do arquivo
	 * @return boolean
	 */
	public function truncate( $size = 0 );

	/**
	 * Escreve uma porção de bytes no arquivo
	 * @param string $data Os dados que serão gravados
	 * @return boolean
	 */
	public function write( $data );
}