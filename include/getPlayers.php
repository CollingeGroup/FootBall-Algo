<?php

// include the config file
include("../config.php");

// get all the team info from the API
$query = $db->query("SELECT _links_players,team_id FROM teams");
$teams = $query->fetchAll();

// loop through this information and grab the players
foreach ($teams as $players) {
    // set the URI via the link from the database
    $uri = $players['_links_players'];
    $reqPrefs['http']['method'] = 'GET';
    $reqPrefs['http']['header'] = 'X-Auth-Token: '.$API;
    $stream_context = stream_context_create($reqPrefs);
    $response = file_get_contents($uri, false, $stream_context);
    $fixtures = json_decode($response, true);

    //check headers to make sure we have the results - also see what limit anmd reset value are
    $header = $http_response_header['0'];
    $limit = str_replace("X-Requests-Available: ", "", $http_response_header['5']);
    $reset = str_replace("X-RequestCounter-Reset: ", "", $http_response_header['6']);

    // if we get close to our limit we need to sleep for the reset time to start again
    if ($limit < '5') {
        sleep($reset);
    } 
    // check to make sure we have the results
    if ($header == 'HTTP/1.1 200 OK') {
        foreach ($fixtures['players'] as $player) {
            // get all variables we need 
            $name = str_replace("'", "", $player['name']);
            $position = str_replace("'", "", $player['position']);
            $jerseyNumber = str_replace("'", "", $player['jerseyNumber']);
            $dateOfBirth = str_replace("'", "", $player['dateOfBirth']);
            $nationality = str_replace("'", "", $player['nationality']);
            $contractUntil = str_replace("'", "", $player['contractUntil']);
            $marketValue = str_replace(",", "", str_replace("€", "" ,str_replace("'", "", $player['marketValue'])));

            // get the teams ID so we know what team this player plays for
            $team_id = $players['team_id'];

            // make sure that we dont duplicate the players
            $query = $db->query("SELECT * FROM players WHERE playerName = '$name' and PlayerDOB = '$dateOfBirth'");
            $count = $query->rowCount();

            // we dont have a duplicate of the player so add this player to the database
            if ($count == '0') {
                // insert all these variables into the database
                $query = $db->query("INSERT INTO players (playerName,PlayerPosition,PlayerNumber,PlayerDOB,PlayerNationality,ContractUntil,PlayerMarketValue,teams_id)VALUES
                   ('$name','$position','$jerseyNumber','$dateOfBirth','$nationality','$contractUntil','$marketValue','$team_id')");
            }
        }
    }
}
