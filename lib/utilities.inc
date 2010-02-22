<?php
function fetchParams($engine_in)
{
    $params_file = 'template_engines/' . $engine_in . '/params.json';

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

function site_uri($view, $action = null, $isSecure = true)
{
	$url = (($isSecure && !APP_FORCE_INSECURE) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . APP_URI . '/';
	$clean_parts = '';

	if (isset($_REQUEST['engine']))
	{
		$_SESSION['template_engine'] = $_REQUEST['engine'];
	}

	$url_parts['view'] = $view;
	$clean_parts .= $view;

	if (!is_null($action))
	{
		$url_parts['action'] = $action;
		$clean_parts .= '/' . $action;
	}

	if (APP_CLEAN_URL === true)
	{
		$url .= $clean_parts;
	}
	else
	{
		$url .= 'index.php?' . http_build_query($url_parts);
	}
	
	return $url;
}
