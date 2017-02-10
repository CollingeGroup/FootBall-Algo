<?php

// include the config
include("../config.php");

// get all the teams link from the competitions database
$query = $db->query("SELECT _links_teams,comp_id FROM competitions");
$comps = $query->fetchAll();

// loop through all these results
foreach ($comps as $teamLink) {

    // get all the information from the API
    $uri = $teamLink['_links_teams'];
    $reqPrefs['http']['method'] = 'GET';
    $reqPrefs['http']['header'] = 'X-Auth-Token: '.$API;
    $stream_context = stream_context_create($reqPrefs);
    $response = file_get_contents($uri, false, $stream_context);
    $fixtures = json_decode($response, true);
    
    //check headers to make sure we have the results - since we are limited make sure we have not hit our limit and reset if we get close
    $header = $http_response_header['0'];
    $limit = str_replace("X-Requests-Available: ", "", $http_response_header['5']);
    $reset = str_replace("X-RequestCounter-Reset: ", "", $http_response_header['6']);

    // if we get close to our limit we need to sleep for the reset time to start again
    // sleep at 4 so we know we have the results - if we sleep anylower we might clash with the other scripts that are running and not get the results we need
    if ($limit < '5') {
        sleep($reset);
    } 

    // check to make sure we have the results
    if ($header == 'HTTP/1.1 200 OK') {
        foreach ($fixtures['teams'] as $teams) {
            // get all variables we need 
            $_links_self = str_replace("'", "", $teams['_links']['self']['href']);
            $_links_fixtures = str_replace("'", "", $teams['_links']['fixtures']['href']);
            $_links_players = str_replace("'", "", $teams['_links']['players']['href']);
            $name = str_replace("'", "", $teams['name']);
            $code = str_replace("'", "", $teams['code']);
            $shortName = str_replace("'", "", $teams['shortName']);
            $squadMarketValue = str_replace("€", "", str_replace("'", "", $teams['squadMarketValue']));
            $crestUrl = str_replace("'", "", $teams['crestUrl']);

            // get the competitions ID so we know what competition the team plays in
            $comp_id = $teamLink['comp_id'];

            // need a UNIQUE ID for the teams instead of making one we will just use the one from the API URL 
            $teamID = array_reverse(explode("/", $_links_self));

            // set the UNIQUE team ID
            $teamID = $teamID['0'];

            // insert all these variables into the database
            $query = $db->query("INSERT INTO teams (_links_self,_links_fixtures,_links_players,name,code,shortname,squadmarketvalue,creastURL,comp_id,team_id)VALUES
                ('$_links_self','$_links_fixtures','$_links_players','$name','$code','$shortName','$squadMarketValue','$crestUrl','$comp_id','$teamID')");
        }
    }
}
