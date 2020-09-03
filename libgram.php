<?php

/**
* Project Name: Lib Telegram Generator untuk Google Apps Script 
* Language Code : PHP
*/

//curl method get
function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

//https://stackoverflow.com/a/2790919
function start_with($string, $query) {
	return substr($string, 0, strlen($query)) === $query;
}

function get_telegram_api($sub) {
	$response = curl_get("https://aghisna.xyz/telegram.php?sub=".urlencode($sub));
	return json_decode($response, true);
}

function get_header_info() {
	$sekarang = new DateTime("now", new DateTimeZone('Asia/Jakarta'));
	$sekarang = $sekarang->format('Y-m-d H:i:s');
	$cur_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$hasil  = "/**\n*This library was generated.";
	$hasil .= "\n*Date : ".$sekarang."\n*/\n\n";
	return $hasil;
}

function build_class() {
	$types = get_telegram_api('Available types');
	$hasils = '';
	if (isset($types['Available types'])) {
		$hasil = '';
		foreach ($types['Available types'] as $key => $value) {
			$nama_class = $value['type'];
			$deskripsi_class = $value['description'];
			$isi_class = '';
			if (isset($value['data'])) {
				$isi_class .= "\n\t\ttypedef std::shared_ptr&lt;".$nama_class."&gt; Ptr;";
				$data_class = $value['data'];
				foreach ($data_class as $keys => $values) {
					$nama_field = $values['field'];
					$type_field = $values['type'];
					if (strpos($type_field, " of ") !== false || strpos($type_field, " or ") !== false) {
						$type_field = "//*".$type_field;
					}
					if ($type_field == 'Integer') {
						$type_field = 'int';
					}
					if ($type_field == 'Boolean') {
						$type_field = 'bool';
					}
					if ($type_field == 'String') {
						$type_field = 'std::string';
					}
					if (start_with($type_field, '<a href=')) {
						$type_field = $type_field."::Ptr";
					}
					$description_field = $values['description'];
					if (strpos($description_field, "<br>") !== false) {
						$description_field = str_replace("<br>", "\n\t\t//", $description_field);
					}
					if (strpos($description_field, "<strong>Note:</strong>") !== false) {
						$description_field = str_replace("<strong>Note:</strong>", "\n\t\t//", $description_field);
					}
					if (strpos($description_field, "<em>Optional</em>. ") !== false) {
						$isi_class .= "\n\n\t\t//".str_replace("<em>Optional</em>. ", "", $description_field);
						$isi_class .= "\n\t\t".$type_field." ".$nama_field.";//<em>Optional</em>";
					} else {
						$isi_class .= "\n\n\t\t//".$description_field;
						$isi_class .= "\n\t\t".$type_field." ".$nama_field.";";
					}
				}
			}
			if  (strpos($nama_class, " ") !== false) {
				$hasil .= "/**\n*".$deskripsi_class."\n*\n";
				$hasil .= "*class ".$nama_class." {";
				$hasil .= "\n*\tpublic:";
				$hasil .= $isi_class;
				$hasil .= "\n*};\n*/";
			} else {
				$hasil .= "/**\n*".$deskripsi_class."\n*/\n";
				$hasil .= "class ".$nama_class." {";
				$hasil .= "\n\tpublic:";
				$hasil .= $isi_class;
				$hasil .= "\n};\n\n";
			}
		}
		$hasils .= "<pre>#ifndef TYPES_H\n#define TYPES_H\n\n";
		$hasils .= "namespace Types {\n\n".$hasil."\n}\n\n#endif //TYPES_H</pre>";
	}
	echo $hasils;
}

function build_method($lang = 'c') {
	$methods = get_telegram_api('Available methods');
	if (isset($methods['Available methods'])) {
		$hasils = '';
		foreach ($methods['Available methods'] as $key => $value) {
			$nama_method = $value['method'];
			$deskripsi_method = $value['description'];
			$param_method = '';
			$param_object = '';
			if (isset($value['data'])) {
				$data_method = $value['data'];
				foreach ($data_method as $keys => $values) {
					$nama_param = $values['parameter'];
					$type_param = $values['type'];
					if ($lang == 'c') {
						if (strpos($type_param, " of ") !== false || (strpos($type_param, " or ") !== false && $type_param != "Integer or String")) {
							$type_param = "//*".$type_param;
						}
						if ($type_param == 'Integer or String') {
							$type_param = 'std::string';
						}
						if ($type_param == 'Float number') {
							$type_param = 'float';
						}
						if ($type_param == 'Integer') {
							$type_param = 'int';
						}
						if ($type_param == 'Boolean') {
							$type_param = 'bool';
						}
						if ($type_param == 'String') {
							$type_param = 'std::string';
						}
					}
					$required_param = $values['required'];
					/* $description_param = $values['description'];
					if (strpos($description_param, "<br>") !== false) {
						$description_param = str_replace("<br>", "\n\t\t//", $description_param);
					}
					if (strpos($description_param, "<strong>Note:</strong>") !== false) {
						$description_param = str_replace("<strong>Note:</strong>", "\n\t\t//", $description_param);
					}
					$param_method .= "\n\t\t//".$description_param; */
					if ($lang == 'c') {
						$param_method .= "\n\t\t".$type_param." ".$nama_param;
					} elseif ($lang == 'javascript') {
						$param_method .= "\n\t\t".$nama_param;
						$param_object .= "\n\t\t\t'".$nama_param."':".$nama_param;
					}
					if ($keys != count($data_method) - 1) {
						if ($lang == 'c') {
							if ($required_param == 'Optional') {
								$param_method .= ", //".$required_param."\n";
							} else {
								$param_method .= ",\n";
							}
						} elseif ($lang == 'javascript') {
							/* if ($required_param == 'Optional') {
								$param_method .= ", //".$type_param." (".$required_param.")\n";
							} else {
								$param_method .= ", //".$type_param."\n";
							} */
							$param_method .= ",";
							$param_object .= ",";
						}
					} else {
						if ($required_param == 'Optional') {
							if ($lang == 'c') {
								$param_method .= " //".$type_param;
							} elseif ($lang == 'javascript') {
								// $param_method .= " //".$type_param." (".$required_param.")";
							}
						}
					}
				}
			}
			if (strpos($nama_method, " ") !== false) {
				$hasil  = "\t/**\n\t*".$deskripsi_method."\n\t*\n";
				$hasil .= "\t*".$nama_method."(";
				if ($param_method != '') {
					$hasil .= $param_method."\n\t* ~~~~"
					;
				}
				$hasil .= ") {\n\t*}\n\t*/\n\n";
				$hasils .= $hasil;
			} else {
				$hasil  = "\t/**\n\t*".$deskripsi_method."\n\t*/\n";
				$hasil .= "\t".$nama_method."(";
				if ($param_method != '') {
					$hasil .= $param_method."\n\t";
				}
				if ($param_object != '') {
					$hasil .= ") {\n\t\treturn this.request('".$nama_method."', this.buildQuery({".$param_object."\n\t\t}));\n\t}\n\n";
				} else {
					$hasil .= ") {\n\t\treturn this.request('".$nama_method."');\n\t}\n\n";
				}
				$hasils .= $hasil;
			}
		}
		if ($lang == 'c') {
			echo "<pre>".get_header_info()."class Telegram {\npublic:\n\n".$hasils."}</pre>";
		} elseif ($lang == 'javascript') {
			$constructor_func = "\t/**\n\t*initialize constructor\n\t*/\n\tconstructor(token) {\n\t\tthis.token = token;\n\t\tthis.urlapi = 'https://api.telegram.org/bot';\n\t}\n\n";
			$request_api_func = "\t/**\n\t*request api telegram\n\t*/\n\trequest(method, data, form, blob) {\n\t\tif (!this.token) {\n\t\t\treturn 'Bot Token is required';\n\t\t}\n\t\tif (!method) {\n\t\t\treturn 'Method is required';\n\t\t}\n\t\tvar options = {\n\t\t\t'method':'post',\n\t\t\t'contentType':'application/json'\n\t\t};\n\t\tif (data) {\n\t\t\toptions['payload'] = JSON.stringify(data);\n\t\t\tif (form) options['payload'] = data;\n\t\t}\n\t\tvar response = UrlFetchApp.fetch(this.urlapi + this.token + '/' + method, options);\n\t\tif (response.getResponseCode() == 200) {\n\t\t\tif (blob) {\n\t\t\t\treturn response.getBlob();\n\t\t\t} else {\n\t\t\t\treturn JSON.parse(response.getContentText());\n\t\t\t}\n\t\t}\n\t\treturn false;\n\t}\n\n";
			$build_query_func = "\t/**\n\t*build query from array\n\t*/\n\tbuildQuery(array) {\n\t\tvar query = {}\n\t\tif (array) {\n\t\t\tfor (var index in array) {\n\t\t\t\tif (array[index]) {\n\t\t\t\t\tvar value = array[index];\n\t\t\t\t\tif (index == 'options') {\n\t\t\t\t\t\tfor (var ix in value) {\n\t\t\t\t\t\t\tif (value[ix]) {\n\t\t\t\t\t\t\t\tquery[ix] = value[ix];\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t} else {\n\t\t\t\t\t\tquery[index] = value;\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\t\t}\n\t\treturn query;\n\t}\n\n";
			echo "<pre style='tab-size:4;'>".get_header_info()."class Telegram {\n\n".$constructor_func.$request_api_func.$build_query_func.$hasils."}</pre>";
		}
	}
}

//build_class();
if (isset($_GET['source'])) {
	$sc = $_GET['source'];
	echo build_method($sc);
}
if (isset($_GET['class'])) {
	$cls = $_GET['class'];
	echo build_class($cls);
}

?>
