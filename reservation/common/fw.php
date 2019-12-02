<?php
//------------------------------------------------------------------------------
// 基盤プログラム
//------------------------------------------------------------------------------

// 日付エラー定数
$iRuleVal = 0;
define ('ERR_STR_DATE_OK', 0);						// 日付として妥当
define ('ERR_STR_DATE_YEAR', ++$iRuleVal);			// 年エラー
define ('ERR_STR_DATE_MONTH', ++$iRuleVal);			// 月エラー
define ('ERR_STR_DATE_DAY', ++$iRuleVal);			// 日エラー

// 時間エラー定数
define ('ERR_STR_HMS_OK', 0);						// 時刻として妥当
define ('ERR_STR_HMS_HOUR', ++$iRuleVal);			// 時エラー
define ('ERR_STR_HMS_MINUTE', ++$iRuleVal);			// 分エラー
define ('ERR_STR_HMS_SECOND', ++$iRuleVal);			// 秒エラー
$iRuleVal = 0;
// 文字列変換定数
define ('STR_ENC_NONE', 0);		// 変換なし
define ('STR_ENC_SYS_TO_HTML', ++$iRuleVal);		// システム文字列(\n 等)をHTML文字列(<BR>\n 等)に変換
define ('STR_ENC_HTML_TO_SYS', ++$iRuleVal);		// HTML文字列(<BR>\n 等)をシステム文字列(\n 等)に変換
define ('STR_ENC_SYS_TO_HTML_PLAIN', ++$iRuleVal);	// システム文字列(\n 等)を単純なHTML文字列(<BR> 等)に変換
define ('STR_ENC_HTML_TO_SYS_PLAIN', ++$iRuleVal);	// 単純なHTML文字列(<BR> 等)をシステム文字列(\n 等)に変換
$iRuleVal = 0;
unset($iRuleVal);


//------------------------------------------------
// 相対パスリダイレクト関数
// 引数：$path = 相対パス
//------------------------------------------------
function relative_redirect($extra)
{
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/$extra");
	exit;
}

//------------------------------------------------
// 禁止文字チェック関数
// 禁止文字あり＝false、禁止文字なし＝true
//------------------------------------------------
function checkProhibitString($str)
{
	$pattern = "/[" . PROHIBIT_STRING_ASCII . "]/";
	if (preg_match($pattern, $str)) { return false; }
	return true;
}

//------------------------------------------------
// セレクトボックス取得関数
// 例）$str = getSelectBox("name", "key", "multiple", $list);
// 引　数：$name      = メニュー名
// 　　　　$selectKey = 初期表示に選択する項目の内容
// 　　　　$opt       = selectタグに追加する情報
// 　　　　$list      = 項目表示内容
// 　　　　$makeBlank = 初期空白設定
// 　　　　$setValue  = false → value="KEY" ,  true → value="VALUE"
// 戻り値：セレクトボックス文字列
//------------------------------------------------
function getSelectBox($name, $selectKey = "", $opt = "", $list, $makeBlank = false, $setValue = false)
{
	$str  = "<select name='".$name."' ".$opt.">";
	$sel = "";
	// 初期表示が指定されていない場合、１つ目を選択
	if ($selectKey == "") { $sel = "selected=\"selected\""; }
	if ($makeBlank) {
		$str .= "<option value='' ".$sel.">&nbsp;</option>";
		$sel = "";
	}
	while(is_array($list) && list($key, $val) = each($list)) {
		if($setValue) {
			if (mb_strlen($selectKey) > 0 && $selectKey == $val) { $sel = "selected=\"selected\""; }
			$str .= "<option value='".$val."' ".$sel.">".$val."</option>";
		} else {
			if (mb_strlen($selectKey) > 0 && $selectKey == $key) { $sel = "selected=\"selected\""; }
			$str .= "<option value='".$key."' ".$sel.">".$val."</option>";
		}
		$sel = "";
	}
	$str .= "</select>";

	return $str;
}

//------------------------------------------------
// 関数名：正規表現関数
//------------------------------------------------
function regularExpression($pattern, $str, $mode = 'ereg')
{
	$res = false;
	if ($mode == 'ereg') {
		$res = mb_ereg($pattern, $str);
	} else if ($mode == 'preg') {
		$pattern = sprintf("/%s/", $pattern);
		$res = preg_match($pattern, $str);
	}
	return $res;
}

//------------------------------------------------
// 関数名：文字列チェック　半角文字
//------------------------------------------------
function isAscii($str)
{
	$pattern = "^[\x01-\x7F]+$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　半角アルファべット
//------------------------------------------------
function isAlphabet($str)
{
	$pattern = "^[a-zA-Z]+$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　全角カナ
//------------------------------------------------
function isZenkakuKana($str)
{
	$pattern = "^[ァ-ヶー－ 　]+$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　通貨
//------------------------------------------------
function isCurrency($str)
{
	$lst = array(",", "\\", "$");
	$res = str_replace($lst, "", $str);
	if (is_numeric($res)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　メールアドレス
//------------------------------------------------
function isMailAddress($str)
{
	$pattern = "^[A-Za-z0-9_\-.]+@[A-Za-z0-9_\-.]+\.[A-Za-z0-9_\-]+$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　電話番号
//------------------------------------------------
function isPhoneNumber($str)
{
	$pattern = "^[0-9]{1,4}-?[0-9]{1,6}-?\d+$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}
//------------------------------------------------
// 関数名：文字列チェック　郵便番号
//------------------------------------------------
function isZip($str)
{
	$pattern = "^[0-9]{3}-?[0-9]{4}$";
	if (regularExpression($pattern, $str)) { return true; }
	return false;
}

//------------------------------------------------
// 関数名：文字列チェック　日時
//------------------------------------------------
// 年月 (yyyyMM＝Ym)
function isDate6($str)
{
	if (strlen($str) != 6) { return false; }

	$y = substr($str, 0, 4); // yyyy
	$m = substr($str, 4, 2); // MM
	$d = "01"; // dd

	if (fncCheckDate($y, $m, $d) !== ERR_STR_DATE_OK) { return false; }

	return true;
}
// 年月日 (yyyyMMdd＝Ymd)
function isDate($str)
{
	if (strlen($str) != 8) { return false; }

	$y = substr($str, 0, 4); // yyyy
	$m = substr($str, 4, 2); // MM
	$d = substr($str, 6, 2); // dd

	if (fncCheckDate($y, $m, $d) !== ERR_STR_DATE_OK) { return false; }

	return true;
}
// 時分 (HHmm＝Hi)
function isHM($str)
{
	if (strlen($str) != 4) { return false; }

	$h = substr($str, 0, 2); // HH
	$m = substr($str, 2, 2); // mm
	$s = "00"; // ss

	if (fncCheckTime($h, $m, $s) !== ERR_STR_HMS_OK) { return false; }

	return true;
}
// 時分秒 (HHmmss＝His)
function isHMS($str)
{
	if (strlen($str) != 6) { return false; }

	$h = substr($str, 0, 2); // HH
	$m = substr($str, 2, 2); // mm
	$s = substr($str, 4, 2); // ss

	if (fncCheckTime($h, $m, $s) !== ERR_STR_HMS_OK) { return false; }

	return true;
}
// 日時 (yyyyMMddHHmmss＝YmdHis)
function isDate14($str)
{
	if (strlen($str) != 14) { return false; }

	$ymd = substr($str, 0, 8); // yyyyMMdd
	$hms = substr($str, 8, 6); // HHmmss

	if (!isDate($ymd)) { return false; }
	if (!isHMS($hms)) { return false; }

	return true;
}
// 時刻 (HH:mm＝H:i)
function isTime($str)
{
	$len = strlen($str);
	// 最小＆最大チェック 0:0＝３桁、00:00＝５桁
	if ($len < 3 || $len > 5) { return false; }

	// 書式チェック
	$pattern5 = "^\d{1,2}:\d{1,2}$"; // 00:00
	$fg5 = regularExpression($pattern5, $str);
	if (!$fg5) { return false; }
	
	// 分解
	$obj  = explode(":", $str);
	
	$hour = sprintf("%02d", $obj[0]); // 時
	$min  = sprintf("%02d", $obj[1]); // 分
	$sec  = "00"; // 秒
	
	if (fncCheckTime($hour, $min, $sec) !== ERR_STR_HMS_OK) { return false; }

	return true;
}
// 時刻 (HH:mm:ss＝H:i:s)
function isTime6($str)
{
	$len = strlen($str);
	// 最小＆最大チェック 0:0:0＝５桁、00:00:00＝８桁
	if ($len < 5 || $len > 8) { return false; }

	// 書式チェック
	$pattern8 = "^\d{1,2}:\d{1,2}:\d{1,2}$"; // 00:00:00
	$fg8 = regularExpression($pattern8, $str);
	if (!$fg8) { return false; }
	
	// 分解
	$obj  = explode(":", $str);
	
	$hour = sprintf("%02d", $obj[0]); // 時
	$min  = sprintf("%02d", $obj[1]); // 分
	$sec  = sprintf("%02d", $obj[2]); // 秒
	
	if (fncCheckTime($hour, $min, $sec) !== ERR_STR_HMS_OK) { return false; }

	return true;
}

//------------------------------------------------
// 関数名：文字列チェック　年月日
// 戻り値：日付エラー変数
//------------------------------------------------
function fncCheckDate($y, $m, $d)
{
	// 年チェック
	if (!is_numeric($y)) { return ERR_STR_DATE_YEAR; }
	if ($y < 1) { return ERR_STR_DATE_YEAR; }
	// 月チェック
	if (!is_numeric($m))   { return ERR_STR_DATE_MONTH; }
	if ($m < 1 || $m > 12) { return ERR_STR_DATE_MONTH; }
	// 日チェック
	if (!is_numeric($d)) { return ERR_STR_DATE_DAY; }
	// 年月日チェック
	if (!checkdate($m, $d, $y)) { return ERR_STR_DATE_DAY; }
	return ERR_STR_DATE_OK;
}
//------------------------------------------------
// 関数名：文字列チェック　時間
// 戻り値：時刻エラー変数
//------------------------------------------------
function fncCheckTime($h, $m, $s)
{
	// 時チェック
	if (!is_numeric($h))   { return ERR_STR_HMS_HOUR; }
	if ($h < 0 || $h > 23) { return ERR_STR_HMS_HOUR; }
	// 分チェック
	if (!is_numeric($m))   { return ERR_STR_HMS_MINUTE; }
	if ($m < 0 || $m > 59) { return ERR_STR_HMS_MINUTE; }
	// 秒チェック
	if (!is_numeric($s))   { return ERR_STR_HMS_SECOND; }
	if ($s < 0 || $s > 59) { return ERR_STR_HMS_SECOND; }
	return ERR_STR_HMS_OK;
}

//------------------------------------------------
// 文字列変換関数
//------------------------------------------------
function getFormatString($str, $escape)
{
	//htmlspecialchars() htmlentities()

	// エスケープ文字の取り扱い
	switch ($escape) {
		case STR_ENC_NONE:
			break;

		case STR_ENC_SYS_TO_HTML:	// システム文字列(\r\n 等)をHTML文字列(<br />\r\n 等)に変換
			// 改行記号に<br/>付加
			$str = nl2br($str);
			break;
		case STR_ENC_HTML_TO_SYS:	// HTML文字列(<br />\n 等)をシステム文字列(\n 等)に変換
			// <br>\nから<br>除去
			$str = preg_replace("/<br *\/*>\r\n/i", "\r\n", $str);
			$str = preg_replace("/<br *\/*>\n/i", "\n", $str);
			$str = preg_replace("/<br *\/*>\r/i", "\r", $str);
			$str = preg_replace("/<br *\/*>/i", "\n", $str);
			break;

		case STR_ENC_SYS_TO_HTML_PLAIN:	// システム文字列(\n 等)を単純なHTML文字列(<br /> 等)に変換
			// 改行記号を<br />に置換
			$str = nl2br($str);
			$str = str_replace("\r", "", $str);
			$str = str_replace("\n", "", $str);
			break;
		case STR_ENC_HTML_TO_SYS_PLAIN:	// 単純なHTML文字列(<br /> 等)をシステム文字列(\n 等)に変換
			// <br />を\nに置換
			$str = preg_replace("/<br *\/*>/i", "\n", $str);
			break;
	}

	return $str;
}
