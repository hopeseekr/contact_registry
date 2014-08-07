<?php
function fetchParams($engine)
{
	static $params;

	if (!is_null($params))
	{
		return $params;
	}

	$params_file = realpath('template_engines/' . $engine . '/params.json');

	if (($data = file_get_contents($params_file)) == null)
	{
		throw new Exception('Could not load ' . $params_file);
	}

	if (($params = json_decode($data)) === false)
	{
		throw new Exception('Could not decode JSON: ' . $params_file);
	}

	return $params;
}


?>
