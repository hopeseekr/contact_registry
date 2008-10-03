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
