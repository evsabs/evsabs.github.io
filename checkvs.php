<?php
header('Content-Type: text/html; charset=cp1251');

function httpPostSoap($url, $postData)
{

    $headers = array(
        "Content-type: text/xml;charset=\"utf-8\"",
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($postData),
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // the SOAP request
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function httpPost($url, $params) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}


function parseResponse($html) {
  $html = str_replace( '<div class="descImg aImg"></div>', '', $html );
  $html = preg_replace( '/<div class="descBlock">(.*?)<\/div>/si', '', $html );

  preg_match('/<div class=["\']*message["\']*.*?>(.*?)<\/div.*?>/si', $html, $matches);
  preg_match('/<b>(.*?)<\/b>/', $matches[0], $matches2);

  echo $matches2[1];
}

$urlAbs = 'https://polis.sgabs.ru:8779/insurance_group_service/action';
$urlSmo = 'http://www.spasenie-med.ru/oms/look_out.php';

//$vs_number = $_POST['check_vs'];
$vs_number = 157999999;
$id_autorization = 456267;
$check_vs_xml =
  '<vs_number xsi:type="xsd:int">'.$vs_number.'</vs_number>' .
  '<id_autorization xsi:type="xsd:int">'.$id_autorization.'</id_autorization>';
  $name_str = 'check_vs';

  $xml_data = '<?xml version="1.0" encoding="utf-8"?>
  <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:WashOut">
     <soapenv:Header/>
     <soapenv:Body>
        <urn:'.$name_str.' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'.$check_vs_xml.'</urn:'.$name_str.'>
     </soapenv:Body>
  </soapenv:Envelope>';


  $response = httpPostSoap($urlAbs, $xml_data);

  $Envelope = new SimpleXMLElement($response);
  $response = $Envelope->xpath('//value')[0];

  if (strripos($response, 'неправильный') !== false) {
    $res = httpPost($urlSmo, array('number' => $vs_number));

    $parsedRes = parseResponse($res);
    var_dump($response);
    var_dump($parsedRes);
    if (stripos($parsedRes, 'не выдавался') !== false) {
      echo 'Полис с данным номером не зарегистрирован в системе. Проверьте правильность написание номера. Возможно у Вас страховая компания не входящая в список. Сервис проверят только полисы страховых компаний "АК БАРС-Мед" и СМО "Спасение"';
    } else {
      echo $parsedRes;
    }
  } else {
    echo $response;
  }
