<?php

// include the config file
include("../config.php");

// get the leagure tables from the API
$query = $db->query("SELECT _links_leaguetables,comp_id,id FROM competitions");
$comps = $query->fetchAll();

foreach ($comps as $leagueLink) {

    $uri = $leagueLink['_links_leaguetables'];
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
        // we want to skip the European Championships France 2016 as its messes with all the other tables - has a wierd layout
        if ($fixtures['leagueCaption'] != 'European Championships France 2016') {
            foreach ($fixtures['standing'] as $standings) {
                // now we have all the teams standings
                $team_link = $standings['_links']['team']['href'];
                $position = $standings['position'];
                $playedGames = $standings['playedGames'];
                $points = $standings['points'];
                $goals = $standings['goals'];
                $goalsAgainst = $standings['goalsAgainst'];
                $goalDifference = $standings['goalDifference'];
                $wins = $standings['wins'];
                $draws = $standings['draws'];
                $losses = $standings['losses'];
                $HomeGoals = $standings['home']['goals'];
                $HomeGoalsAgainst = $standings['home']['goalsAgainst'];
                $HomeWins = $standings['home']['wins'];
                $HomeDraws = $standings['home']['draws'];
                $HomeLosses = $standings['home']['losses'];
                $AwayGoals = $standings['away']['goals'];
                $AwayGoalsAgainst = $standings['away']['goalsAgainst'];
                $AwayWins = $standings['away']['wins'];
                $AwayDraws = $standings['away']['draws'];
                $AwayLosses = $standings['away']['losses'];
                $teamLink = $standings['_links']['team']['href'];

                // get the comp ID so we know what the team stats are in what comp incase 1 teams plays in 2 leagues
                $comp_id = $leagueLink['comp_id'];

                // get the UNIQUE ID from the team
                $teamID = array_reverse(explode("/", $team_link));

                // set the UNIQUE team ID
                $teamID = $teamID['0'];

                // now insert the standings of the team into the database
                $query = $db->query("INSERT INTO teamStandings (position,playedGames,points,goals,goalsAgainst,goalDifference,wins,draws,losses,home_goals,home_goalsAgainst,home_wins,home_draws,home_losses,away_goals,away_goalsAgainst,away_wins,away_draws,away_losses,team_id,comp_id)VALUES
                    ('$position','$playedGames','$points','$goals','$goalsAgainst','$goalDifference','$wins','$draws','$losses','$HomeGoals','$HomeGoalsAgainst','$HomeWins','$HomeDraws','$HomeLosses','$AwayGoals','$AwayGoalsAgainst','$AwayWins','$AwayDraws','$AwayLosses','$teamID','$comp_id')");           
            }
        }
    }
}
