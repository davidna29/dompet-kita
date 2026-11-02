<?php

$png = file_get_contents(__DIR__.'/../public/icons/favicon-32.png');
$header = pack('vvv', 0, 1, 1);
$entry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 22);
file_put_contents(__DIR__.'/../public/favicon.ico', $header.$entry.$png);

echo filesize(__DIR__.'/../public/favicon.ico')." bytes\n";
