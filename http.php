<?php

/**
 * http object
 */

namespace curl;

class http
{
	var $uri;
	var $protocol;
	var $port;
	var $body;
	var $header;
	var $get;
	var $cookies;

	public function __construct()
	{
		$this->uri		 = null;
		$this->protocol	 = null;
		$this->port		 = null;
		$this->body		 = null;
		$this->header	 = array();
		$this->get		 = null;
		$this->path		 = null;
		$this->cookies	 = null;
	}
}
