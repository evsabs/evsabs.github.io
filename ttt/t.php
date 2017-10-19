<?php
error_reporting(-1);
header('Content-Type: text/html; charset=cp1251');

$html= file_get_contents( 'html.txt' );
$html = str_replace( '<div class="descImg aImg"></div>', '', $html );
$html = preg_replace( '/<div class="descBlock">(.*?)<\/div>/si', '', $html );
preg_match('/<div class=["\']*message["\']*.*?>(.*?)<\/div.*?>/si', $html, $matches);
preg_match('/<b>(.*?)<\/b>/', $matches[0], $matches2);

$res = $matches2[1];
echo $res;
