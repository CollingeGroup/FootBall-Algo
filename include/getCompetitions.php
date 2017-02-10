<?php

// inlcude the config file
include("../config.php");

// get the competitions from the API
$uri = 'http://api.football-data.org/v1/competitions';
$reqPrefs['http']['method'] = 'GET';
$reqPrefs['http']['header'] = 'X-Auth-Token: '.$API;
$stream_context = stream_context_create($reqPrefs);
$response = file_get_contents($uri, false, $stream_context);
$fixtures = json_decode($response, true);

// get the header information
$header = $http_response_header['0'];

// check we dont have a 404 or API error
if ($header == 'HTTP/1.1 200 OK') {

    foreach ($fixtures as $value) {
        // get all fixtures and insert into the database
        $_links_self = $value['_links']['self']['href'];
        $_links_teams = $value['_links']['teams']['href'];
        $_links_fixtures = $value['_links']['fixtures']['href'];
        $_links_leaguetables = $value['_links']['leagueTable']['href'];
        $comp_id = $value['id'];
        $caption = $value['caption'];
        $league = $value['league'];
        $year = $value['year'];
        $currentMatchday = $value['currentMatchday'];
        $numberOfMatchdays = $value['numberOfMatchdays'];
        $numberOfTeams = $value['numberOfTeams'];
        $numberOfGames = $value['numberOfGames'];
        $lastUpdated = $value['lastUpdated'];

        // insert these into the database
        $query = $db->query("INSERT INTO competitions (_links_self,_links_teams,_links_fixtures,_links_leaguetables,comp_id,caption,league,year,currentmatchday,numberofmatchdays,numberofteams,numberofgames,lastUpdated)VALUES
            ('$_links_self','$_links_teams','$_links_fixtures','$_links_leaguetables','$comp_id','$caption','$league','$year','$currentMatchday','$numberOfMatchdays','$numberOfTeams','$numberOfGames','$lastUpdated')");
    }
}
