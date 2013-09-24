<?php

namespace Illuminate;

class Jasper
{
	public static $url;
}

namespace Illuminate\Jasper;

use Illuminate\Jasper as Jasper;

class Report
{
	const FORMAT_HTML = 'html';
	const FORMAT_PDF = 'pdf';
	const FORMAT_XLS = 'xls';

	protected $jsessionid;
	protected $jasper_url;
	protected $report_url;
	protected $parameters;
	protected $format;

	protected $request;

	public function __construct($options = array())
	{
		$jsessionid = $report_url = null;
		$jasper_url = Jasper::$url;
		$parameters = array();
		$format = 'html';
		extract($options, EXTR_IF_EXISTS);
		$this->jsessionid = $jsessionid;
		$this->jasper_url = $jasper_url;
		$this->report_url = $report_url;
		$this->parameters = $parameters;
		$this->format = $format;
	}

	public function makeRequest()
	{
		$this->request = new Request(array(
			'uri'        => $this->getUrl(),
			'jasper_url' => $this->jasper_url,
			'jsessionid' => $this->jsessionid,
		));
		$this->request->makeRequest();
		return $this->request;
	}

	public function getHtml($asset_path, $base_url = '/jasperserver')
	{
		if ($this->format !== self::FORMAT_HTML) {
			return false;
		}
		$body = str_replace($base_url, $asset_path, $this->request->getBody());
		return $body;
	}

	public static function getFormat($format)
	{
		$format = strtolower($format);
		$allowed_formats = array(
			self::FORMAT_HTML,
			self::FORMAT_XLS,
			self::FORMAT_PDF
		);
		if ( ! in_array($format, $allowed_formats)) {
			throw new \Exception('Invalid format.');
		}
		return $format;
	}

	protected function getUrl()
	{
		$url_params = http_build_query($this->parameters);
		return "/rest_v2/reports/{$this->report_url}.{$this->format}?{$url_params}";
	}
}

class ReportAsset
{
	protected $jsessionid;
	protected $jasper_url;
	protected $file;

	public function __construct($options = array())
	{
		$jsessionid = $file = null;
		$jasper_url = Jasper::$url;
		extract($options, EXTR_IF_EXISTS);
		$this->jsessionid = $jsessionid;
		$this->jasper_url = $jasper_url;
		$this->file = $file;
	}

	public function output()
	{
		$request = new Request(array(
			'uri'        => $this->file,
			'jasper_url' => $this->jasper_url,
			'jsessionid' => $this->jsessionid,
		));
		$request->makeRequest();

		header("Content-Type: {$request->getHeader('Content-Type')}");
		header("Content-Length: {$request->getHeader('Content-Length')}");
		echo $request->getBody();
		exit;
	}
}

class Request
{
	protected $uri;

	protected $jsessionid;
	protected $jasper_url;

	protected $headers = array();
	protected $body;

	public function __construct($options = array())
	{
		$jsessionid = null;
		$jasper_url = Jasper::$url;
		$uri = '';
		extract($options, EXTR_IF_EXISTS);
		$this->jsessionid = $jsessionid;
		$this->jasper_url = $jasper_url;
		$this->uri = $uri;
	}

	public function makeRequest()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getUrl());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		if ($this->jsessionid) {
			curl_setopt($ch, CURLOPT_COOKIE, "JSESSIONID={$this->jsessionid}");
		}
		$response = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$this->setHeaders($header);
		$this->setJasperCookie($header);
		$this->body = substr($response, $header_size);
		if ($this->body === false) {
			throw new \Exception("cURL error <{$url}>: " . curl_errno($ch) . " - " . curl_error($ch));
		}
		curl_close($ch);
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getHeader($header = null)
	{
		if ( ! $header) {
			return $this->headers;
		}
		return (isset($this->headers[$header]) ? $this->headers[$header] : false);
	}

	public function getJsessionid()
	{
		return $this->jsessionid;
	}

	protected function getUrl()
	{
		$file = ltrim($this->uri, '/');
		$jasper = rtrim($this->jasper_url, '/');
		return "{$jasper}/{$file}";
	}

	protected function setHeaders($header)
	{
		$headers = array();
		foreach (explode("\n", $header) as $header_row) {
			$header_row = trim($header_row);
			if (preg_match("/([\w-._]+): (.*)/", $header_row, $matches)) {
				$headers[$matches[1]] = $matches[2];
			}
		}
		$this->headers = $headers;
	}

	protected function setJasperCookie($header)
	{
		if (preg_match("/JSESSIONID=(\S+);/", $header, $cookie)) {
			$this->jsessionid = $cookie[1];
		}
	}
}