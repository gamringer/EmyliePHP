<?php

namespace Emylie\IO;

use \Psr\Http\Message\StreamableInterface;

class StreamReader implements StreamableInterface
{
	private $stream;

	public function __construct($stream = null)
	{
		if (
		    gettype($stream) != 'resource'
		 || get_resource_type($stream) != 'stream'
		) {
			throw new \InvalidArgumentException('Expected Stream Resource');
		}

		$this->stream = $stream;
	}

	public function __toString()
	{
		$this->seek(0);

		return $this->getContents();
	}

	public function close()
	{
		fclose($this->stream);
	}

	public function detach()
	{
		$stream = $this->stream;
		
		$this->stream = null;

		return $stream;
	}

	public function attach($stream)
	{
		if (
		    gettype($stream) != 'resource'
		 || get_resource_type($stream) != 'stream'
		) {
			throw new \InvalidArgumentException('Expected Stream Resource');
		}

		$this->stream = $stream;
		$this->seek(0);
	}

	public function getSize()
	{
		if ($this->stream == null) {
			return null;
		}
		
		return fstat($this->stream)['size'];
	}

	public function tell()
	{
		if ($this->stream == null) {
			return false;
		}
		
		return ftell($this->stream);
	}

	public function eof()
	{
		if ($this->stream == null) {
			return false;
		}
		
		return feof($this->stream);
	}

	public function isSeekable()
	{
		return (bool) $this->getMetadata('seekable');
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->isSeekable()) {
			return false;
		}
		
		return fseek($this->stream, $offset, $whence) === 0;
	}

	public function isWritable()
	{
		return (bool) $this->getMetadata('seekable');
	}

	public function write($string)
	{
		return is_writeable($this->stream);
	}

	public function isReadable()
	{
		// TODO

		return true;
	}

	public function read($length)
	{
		if (!$this->isReadable()) {
			return false;
		}
		
		return fread($this->stream, $length);
	}

	public function getContents()
	{
		if (!$this->isReadable()) {
			return false;
		}

		return stream_get_contents($this->stream);
	}

	public function getMetadata($key = null)
	{
		if ($this->stream == null) {
			return null;
		}

		$md = stream_get_meta_data($this->stream);

		if($key == null) {
			return $md;
		}


		if (isset($md[$key])) {
			return $md[$key];
		}

		return null;
	}

}