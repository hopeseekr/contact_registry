<?php

abstract class Manager
{
	/**
	 * @var ezcSignalCollection
	 */
	protected $signals;

	private function __construct() { }
	
	/**
	 * @return ezcSignalCollection
	 */
	public function signals()
	{
		if (is_null($this->signals))
		{
			$this->signals = new ezcSignalCollection();
		}
		
		return $this->signals;
	}
}