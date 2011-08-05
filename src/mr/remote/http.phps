<?php
class mr_remote_http {

	private $url;
	private $method = "GET";
	private $cookies = array ();
	private $post = array ();
	private $files = array ();
	private $raw_post;
	private $referer;

	private $code;
	private $msg;
	private $head;
	private $body;

	/**
	 * Создаёт соединение с $url
	 *
	 * @param string $url
	 */
	public function __construct( $url )
	{
		if ($url)
			$this->setURL( $url );
	}

	/**
	 * Устанавливает URL запроса
	 *
	 * @param string $url
	 * @return mr_remote_http
	 */
	public function setURL( $url )
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Метод передачи -- POST
	 *
	 * @return mr_remote_http
	 */
	public function setMethodPost()
	{
		$this->method = "POST";
		return $this;
	}

	/**
	 * Текст для поля POST
	 *
	 * @param string $data
	 * @return mr_remote_http
	 */
	public function setPostRaw( $data )
	{
		$this->raw_post = $data;
		return $this;
	}

	/**
	 * Одно поле POST вида name=value
	 *
	 * @param string $name
	 * @param string $data
	 * @return mr_remote_http
	 */
	public function setPostField( $name, $data )
	{
		$this->post[ $name ] = $data;
		return $this;
	}

	/**
	 * Передаёт кукис
	 *
	 * @param string $name
	 * @param string $data
	 * @return mr_remote_http
	 */
	public function setCookie( $name, $data )
	{
		$this->cookies[ $name ] = $data;
		return $this;
	}

	/**
	 * Файл для аплоада
	 *
	 * @param string $name - ключ типа input name=
	 * @param string $filename
	 * @throws Exception
	 * @return mr_remote_http
	 */
	public function setFileUpload( $name, $filename )
	{
		if (!is_readable( $filename ))
			throw new Exception( "Cannot upload $filename" );

		$this->files[ $name ] = $filename;

		return $this;
	}

	/**
	 * Устанавливает поле Referer
	 *
	 * @param string $url
	 * @return mr_remote_http
	 */
	public function setReferer( $url = "" )
	{
		$this->referer = $url ? $url : "http://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
		return $this;
	}

	/**
	 * Осуществляет запрос к удалённому серверу. Возвращает тело результата
	 *
	 * @return mr_remote_http
	 */
	public function request()
	{
		$request = "";

		$url = parse_url( $this->url );

		$host = ($url[ "host" ] ? $url[ "host" ] : $_SERVER[ 'HTTP_HOST' ]);

		// Основа запроса
		$request .= $this->method . " " . $url[ "path" ] . ($url[ "query" ] ? "?" . $url[ "query" ] : "") . " HTTP/1.1\r\n";
		$request .= "Host: " . $host . "\r\n";

		// Передаём кукисы, если есть
		if (count( $this->cookies )) {
			$request .= "Cookie: ";
			$cs = "";
			foreach ($this->cookies as $name => $value)
				$cs .= ($cs ? "; " : "") . urlencode( $name ) . "=" . urlencode( $value );
			$request .= $cs . "\r\n";
		}

		// Подготавливаем тело запроса
		$request_body = "";

		if ($this->method == "POST") {
			if (count( $this->files )) {

				$boundary = "";
				while (strlen( $boundary ) < 20)
					$boundary .= dechex( mt_rand( 0, 255 ) );
				$boundary = strtoupper( $boundary );

				$request .= "Content-Type: multipart/form-data; boundary=" . $boundary . "\r\n";

				foreach ($this->files as $name => $filename) {
					$file_contents = file_get_contents( $filename );

					$request_body .= "--" . $boundary . "\r\n";
					$request_body .= "Content-disposition: form-data; name=\"" . urlencode( $name ) . "\"; filename=\"" .
										 urlencode( basename( $filename ) ) . "\"\r\n";
					$request_body .= "Content-type: application/octet-stream\r\n";
					$request_body .= "Content-Transfer-Encoding: binary\r\n";
					$request_body .= "Content-length: " . strlen( $file_contents ) . "\r\n";
					$request_body .= "\r\n" . $file_contents . "\r\n";
				}
				foreach ($this->post as $name => $value) {
					$request_body .= "--" . $boundary . "\r\n";
					$request_body .= "Content-disposition: form-data; name=\"" . urlencode( $name ) . "\"\r\n";
					$request_body .= "\r\n" . urlencode( $value ) . "\r\n";
				}

				$request_body .= "--" . $boundary . "--\r\n";

			} else {

				if (count( $this->post ) && !$this->raw_post) {

					$this->raw_post = http_build_query( $this->post );

				} elseif ($this->raw_post)
					$this->raw_post = urlencode( $this->raw_post );

				if (strlen( $this->raw_post )) {
					$request .= "Content-type: application/x-www-form-urlencoded\r\n";
				}

				$request_body = &$this->raw_post;
			}
		}

		// Завершаем запрос
		if ($request_body)
			$request .= "Content-length: " . strlen( $request_body ) . "\r\n";
		if ($this->referer)
			$request .= "Referer: " . $this->referer . "\r\n";
		$request .= "Connection: Close\r\n\r\n";

		$request .= $request_body;

		// Подключение и сбор ответа
		$socket = fsockopen( $host, 80 );

		$return = "";

		fwrite( $socket, $request );
		while (!feof( $socket )) {
			$return .= fgets( $socket, 128 );
		}
		fclose( $socket );

		list ($status, $other) = explode( "\r\n", $return, 2 );
		list ($this->code, $this->msg) = explode( " ", $status, 2 );
		list ($this->head, $this->body) = explode( "\r\n\r\n", $other, 2 );

		return $this;
	}

	/**
	 * Последнее сообщение типа Ok
	 *
	 * @return string
	 */
	public function msg()
	{
		return $this->msg;
	}

	/**
	 * Код последнего ответа
	 *
	 * @return int
	 */
	public function code()
	{
		return $this->code;
	}

	/**
	 * Все заголовки ответа сервера
	 *
	 * @return string
	 */
	public function head()
	{
		return $this->head;
	}

	/**
	 * Возвращает тело ответа сервера
	 *
	 * @return string
	 */
	public function body()
	{
		return $this->body;
	}

}