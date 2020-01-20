<?php

function tydomrequest($request) {
	log::add('tydom', 'debug', "request: " . $request);

    //Initialize cURL.
    $ch = curl_init();
    
    //Set the URL that you want to GET by using the CURLOPT_URL option.
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8080/" . $request);

    //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Execute the request.
    $data = curl_exec($ch);

    //Close the cURL handle.
    curl_close($ch);

    return json_decode($data);
}

?>
