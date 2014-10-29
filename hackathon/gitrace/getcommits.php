<?php

$x = file_get_contents('https://api.github.com/repos/zfcampus/zf-apigility/commits?access_token=d3dd4be6a770aeb3f78d253b6096f36386fcead4');

$data = json_decode($x);
print count($data);