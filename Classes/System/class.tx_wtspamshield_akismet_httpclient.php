<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Alexander Kellner <Alexander.Kellner@einpraegsam.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Used by the Akismet class to communicate with the Akismet service
 *
 * @author Bret Kuhns <@link www.miphp.net>
 * @package tritum
 * @subpackage wt_spamshield
 */
class tx_wtspamshield_akismet_httpclient extends tx_wtspamshield_akismet_object {
	/**
	 * @var string
	 */
	public $akismetVersion = '1.1';

	/**
	 * @var mixed
	 */
	public $con;

	/**
	 * @var string
	 */
	public $host;

	/**
	 * @var integer
	 */
	public $port;

	/**
	 * @var string
	 */
	public $apiKey;

	/**
	 * @var string
	 */
	public $blogUrl;

	/**
	 * @var mixed
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param string $host
	 * @param string $blogUrl
	 * @param string $apiKey
	 * @param integer $port
	 * @return void
	 */
	public function __construct($host, $blogUrl, $apiKey, $port = 80) {
		$this->host = $host;
		$this->port = $port;
		$this->blogUrl = $blogUrl;
		$this->apiKey = $apiKey;
	}

	/**
	 * Use the connection active in $con to get a response from the
	 * server and return that response
	 *
	 * @param mixed $request
	 * @param string $path
	 * @param string $type
	 * @param integer $responseLength
	 * @return mixed
	 */
	public function getResponse($request, $path, $type = 'post', $responseLength = 1160) {
		$this->connect();

		if ($this->con && !$this->isError(AKISMET_SERVER_NOT_FOUND)) {
			$request  =
					strToUpper($type) . ' /' .
					$this->akismetVersion . '/' .
					$path .
					" HTTP/1.1\r\n" .
					'Host: ' .
					( ( !empty($this->apiKey) )
						? $this->apiKey  . '.'
						: NULL
					) .
					$this->host . "\r\n" .
					"Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n" .
					'Content-Length: ' . strlen($request) . "\r\n" .
					"User-Agent: Akismet PHP4 Class\r\n" .
					"\r\n" .
					$request;
			$response = '';

			@fwrite($this->con, $request);

			while (!feof($this->con)) {
				$response .= @fgets($this->con, $responseLength);
			}

			$response = explode("\r\n\r\n", $response, 2);
			return $response[1];
		} else {
			$this->setError(AKISMET_RESPONSE_FAILED, 'The response could not be retrieved.');
		}

		$this->disconnect();
		return NULL;
	}

	/**
	 * Connect to the Akismet server and store that connection in the
	 * instance variable $con
	 *
	 * @return void
	 */
	public function connect() {
		if (!($this->con = @fsockopen($this->host, $this->port))) {
			$this->setError(AKISMET_SERVER_NOT_FOUND, 'Could not connect to akismet server.');
		}
	}

	/**
	 * Close the connection to the Akismet server
	 *
	 * @return void
	 */
	public function disconnect() {
		@fclose($this->con);
	}
}

if (defined('TYPO3_MODE')
	&& isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_akismet_httpclient.php'])
) {
	require_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_akismet_httpclient.php']);
}

?>