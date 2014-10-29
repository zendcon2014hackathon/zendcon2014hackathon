<?php
header('Access-Control-Allow-Origin: *');

function callAPI($function, $params, $querystring = '') {
    $paramString = '';
    foreach ($params as $key=>$value) {
        $paramString .= $key . '/' . $value . '/';
    }
    $ch = curl_init('http://api.joind.in/v2.1/' . $paramString . $function . '?format=json&resultsperpage=200' . ($querystring != '' ? '&' . $querystring : '')); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZendCon 2014 Hackathon');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    if ($result == false) {
        throw new Exception('Call to Joind.in API failed');
    }
    $result = json_decode($result);
    return $result;    
}


switch ($_GET['action']) {
    case 'getevents':
        $events = callAPI('events', array(), 'filter=hot');
        $returnArray = array();
        foreach ($events->events as $event) {
            $returnArray[] = array(
                'id' => array_pop(explode('/', $event->uri)),
                'name' => $event->name
            );
        }
        echo json_encode($returnArray);
        break;
    case 'getspeakers':
        $talks = callAPI('talks', array('events' => $_GET['id']));
        $speakers = array();
        foreach ($talks->talks as $talk) {
            if (isset($talk->speakers) && isset($talk->speakers[0]) && isset($talk->speakers[0]->speaker_uri)) {
                $speakers[array_pop(explode('/', $talk->speakers[0]->speaker_uri))] = $talk->speakers[0]->speaker_name;
            }
        }
        
        $returnArray = array();
        foreach ($speakers as $key=>$value) {
            $returnArray[] = array(
                'id' => $key,
                'name' => $value                  
            );
        }
        echo json_encode($returnArray);
        break;
    case 'gettopratingsforevent':
        $talks = callAPI('talks', array('events' => $_GET['id']));
        $speakers = array();
        $talklist = array();
        foreach ($talks->talks as $talk) {
            if (isset($talk->speakers) && isset($talk->speakers[0]) && isset($talk->speakers[0]->speaker_uri) && $talk->average_rating > 3 && $talk->comment_count > 1) {
                $speakers[array_pop(explode('/', $talk->speakers[0]->speaker_uri))] = $talk->speakers[0]->speaker_name;
                $talklist[array_pop(explode('/', $talk->speakers[0]->speaker_uri))][] = array_pop(explode('/', $talk->uri));
            }
        }

        $returnArray = array();
        foreach ($talklist as $id => $talks) {
            $rating = 0;
            $commentCount = 0;
            $lowestRating = 5;
            $highestRating = 0;
            for ($cnt = 0; $cnt < count($talks); $cnt++) {
                $comments = callAPI('comments', array('talks' => $talks[$cnt]));
                foreach ($comments->comments as $comment) {
                    if ($comment->rating > 0 && isset($comment->user_uri)) { // Speaker comments don't count
                        $rating += $comment->rating;
                        if ($comment->rating < $lowestRating) {
                            $lowestRating = $comment->rating;
                        }
                        if ($comment->rating > $highestRating) {
                            $highestRating = $comment->rating;
                        }
                        $commentCount++;
                    }
                }
            };
            
            $returnArray[] = array(
                'name' => $speakers[$id],
                'avg' => $rating / $commentCount,
                'lowest' => $lowestRating,
                'highest' => $highestRating
            );
        }
        
        echo json_encode($returnArray);
        break;
    case 'getratings':
        $talks = callAPI('talks', array('users' => $_GET['id']));
        $returnArray = array();
        foreach ($talks->talks as $talk) {
            if ($talk->comment_count > 0 && $talk->average_rating > 0) { // Anonymous comments increase comment_count, but average_rating = 0 for those comments
                $returnArray[] = array(
                    'date' => $talk->start_date,
                    'rating' => $talk->average_rating 
                );
            }
        }
        echo json_encode($returnArray);
        break;
}
