<?php 

if (empty($argv[1])) {
	throw new Exception('Введите путь к файлу!');
}

$path = $argv[1];
if (!file_exists($path)) {
	throw new Exception('Файл не найден!');
}

$accessLog = readTheFile($path);

$data = [
	'views'=>null,
	'unicUrls'=>null,
	'traffic'=>null,
	'crawlers'=>[
		'Google'=>null,
		'Bing'=>null,
		'Baidu'=>null,
		'Yandex'=>null
	],
	'statusCodes'=>[]
];
$unicUrls = [];
foreach ($accessLog as $key => $value) {
	if (empty($value)) continue;
	$data['views'] += 1;
	$stringData = myExplode('"', $value);

	$codeAndTrafficString = myTrim($stringData[2]);
	$codeAndTrafficData = myExplode(" ", $codeAndTrafficString);
	if ((int)$codeAndTrafficData[0][0] === 2) {
		$data['traffic'] += $codeAndTrafficData[1];
	}
	if (!array_key_exists($codeAndTrafficData[0],$data['statusCodes'])) {
		$data['statusCodes'][$codeAndTrafficData[0]] = null;
	}
	$data['statusCodes'][$codeAndTrafficData[0]] += 1;

	$deviceString = myTrim($stringData[5]);
	$deviceData = myExplode(" ", $deviceString);

	foreach ($data['crawlers'] as $key2 => $value2) {
		if (strpos(mb_strtolower($deviceData[5]), mb_strtolower($key2)) !== false) {
			$data['crawlers'][$key2] += 1;
		}
	}

	$requestString = myTrim($stringData[1]);
	$requestData = myExplode(" ", $requestString);
	$requestUrl = myTrim($requestData[1]);
	if (!in_array($requestUrl, $unicUrls)) {
		$unicUrls[] = $requestUrl;
	}
}
$data['unicUrls'] = count($unicUrls);

echo json_encode($data);

// ========================== Функции обертки =============================
/**
 * @param string $separator
 * @param string $str
 * @return array
 */
function myExplode(string $separator, string $str) {
	return explode($separator, $str);
}
/**
 * @param string $string
 * @return string
 */
function myTrim(string $str) {
	return trim($str);
}
// ========================================================================
// ========================== Основные функции ============================
/**
 * @param string $path
 * @return array
 */
function readTheFile(string $path) {
  $handle = fopen($path, 'r');
  while(!feof($handle)) {
      yield myTrim(fgets($handle));
  }
  fclose($handle);
}
// ========================================================================
 ?>