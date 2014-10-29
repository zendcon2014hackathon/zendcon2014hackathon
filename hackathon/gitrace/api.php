<?php

switch ($_GET['action']) {
    case 'getproject':
        $c = curl_init('https://api.github.com/search/repositories?access_token=2e90abf397abb4f2df31451adcff5427f868d45f&q=' . $_GET['q']);
        curl_setopt($c, CURLOPT_USERAGENT, 'Firefox');
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($c);
        if ($result == false) {
            echo 'Error';
            break;
        }
        $result = json_decode($result);
        $returnArray = array();
        foreach ($result->items as $item) {
            $returnArray[] = $item->full_name;
        }
        sort($returnArray, SORT_STRING | SORT_FLAG_CASE);
        echo json_encode($returnArray);
        break;
    case 'getcommits':
        $c = curl_init('https://api.github.com/repos/' . $_GET['project1'] . '/commits?access_token=2e90abf397abb4f2df31451adcff5427f868d45f');
        curl_setopt($c, CURLOPT_USERAGENT, 'Firefox');
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $project1result = curl_exec($c);
        if ($project1result == false) {
            echo 'Error';
            break;
        }
        $project1result = json_decode($project1result);
        if (is_null($project1result[0]->author)) {
            echo json_encode(
                array(
                    'error' => 'No valid commits'
                )
            );
            break;
        }
        
        $c = curl_init('https://api.github.com/repos/' . $_GET['project2'] . '/commits?access_token=2e90abf397abb4f2df31451adcff5427f868d45f');
        curl_setopt($c, CURLOPT_USERAGENT, 'Firefox');
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $project2result = curl_exec($c);
        if ($project2result == false) {
            echo 'Error';
            break;
        }
        $project2result = json_decode($project2result);
        if (is_null($project2result[0]->author)) {
            echo json_encode(
                array(
                    'error' => 'No valid commits'
                )
            );
            break;
        }
        
        $project1Return = array();
        foreach ($project1result as $commit) {
            $project1Return[] = array(
                'user' => $commit->author->login,
                'date' => $commit->commit->committer->date
            );
        }

        foreach ($project2result as $commit) {
            $project2Return[] = array(
                'user' => $commit->author->login,
                'date' => $commit->commit->committer->date
            );
        }
        
        echo json_encode(
            array(
                'project1' => $project1Return,
                'project2' => $project2Return
            )
        );
        
        break;
}


// $c = curl_init('https://api.github.com/repos/zfcampus/zf-apigility/commits?access_token=2e90abf397abb4f2df31451adcff5427f868d45f');
// curl_setopt($c, CURLOPT_USERAGENT, 'Firefox');
// curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
// curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
// $result = curl_exec($c);
