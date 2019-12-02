<?php
//------------------------------------------------------------------------------
// システム名：問い合わせフォーム
// 機能名　　：問い合わせ項目設定　拡張機能版
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2010. PLASOL Inc. All Right Reserved.
//------------------------------------------------------------------------------

//--------------------------------------------------
// 設定ファイル読み込み
//--------------------------------------------------
require("common/inc.php");

//--------------------------------------------------
// 初期化
//--------------------------------------------------
$nowDate = date("YmdHis"); // サーバー時刻取得
$version = VERSION_MAIL;
$version_setting = "2.0.1";
$msg_err = "";

$act = $_POST['act'];
if ($act == 'UPD') {
	//--------------------------------------------------
	// ファイル保存
	//--------------------------------------------------
	$lst_data = array();
	$cnt_col = $_POST['col']; // 列数
	$cnt_row = count($_POST['data0']);

	// 行単位でデータ格納
	for ($i = 0;$i < $cnt_row;$i++) {
		$empty_row = ""; // 空行チェック用
		for ($j = 0;$j < $cnt_col;$j++) {
			// 列取得
			$rows = $_POST['data' . $j];

			// 値取得
			$val = $rows[$i];
			// HTML文字列に変換
			$val = convert_data($val);

			// 削除フラグありの場合、スキップ(行削除)
			$del = $_POST['del' . $i];
			if ($del) { continue; }

			// ヘッダーチェック
			$header = $rows[0];
			if ($header == "") { continue; } // ヘッダーが空の場合、スキップ(列削除)

			// 値格納
			$lst_data[$i][$j] = $val;

			// 空行チェック用
			$empty_row .= $val;
		}
		// 空行は除去
		if ($empty_row == "") {
			unset($lst_data[$i]);
		}
	}

	// 保存
	setMailData(FILE_DATA_EX, $lst_data);
}

//--------------------------------------------------
// 追加データ項目の取得
//--------------------------------------------------
$result      = getExData();
$lst_header  = $result['lst_header'];
$lst_ex_data = $result['lst_ex_data'];
unset($result);

// 追加用フィールド
$lst_header[''] = array(); // 空列追加
$lst_ex_data[''] = array(); // 空行追加

// 列数
$cnt_col = count($lst_header);

//--------------------------------------------------
// ヘッダー部生成
//--------------------------------------------------
$html_h  = "<tr>";
$html_h .= "<td>No.</td>";
$html_h .= "<td>削除</td>";
for ($j = 0;list($name, ) = each($lst_header);$j++) {
	$html_h .= "<td><input type='text' name='data{$j}[]' value='$name' style='width:200px;' /></td>";
}
$html_h .= "</tr>\n";
reset($lst_header);

//--------------------------------------------------
// 明細部生成
//--------------------------------------------------
$html_d = "";
for ($i = 0;is_array($lst_ex_data) && list(, $row) = each($lst_ex_data);$i++) {
	$del_row = $i + 1;

	// 行
	$html_d .= "<tr>";
	$html_d .= "<td align='center'>$del_row</td>";
	$html_d .= "<td align='center'><input type='checkbox' name='del{$del_row}[]'></td>";
	while (is_array($lst_header) && list($name, $j) = each($lst_header)) {
		$val = $row[$name];
		$html_d .= "<td><input type='text' name='data{$j}[]' value='{$val}' style='width:200px;' /></td>";
	}
	reset($lst_header);
	$html_d .= "</tr>\n";
}
unset($lst_ex_data);
unset($lst_header);

//--------------------------------------------------
// 一覧生成
//--------------------------------------------------
$html  = "<table border='1' cellspacing='0' cellpadding='0'>\n";
$html .= $html_h;
$html .= $html_d;
$html .= "</table>";

unset($html_h);
unset($html_d);

//--------------------------------------------------
// HTML出力
//--------------------------------------------------
echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="ja">
<head>
<meta name="robots" content="noindex,nofollow" />
<meta http-equiv="Pragma"              content="no-cache" />
<meta http-equiv="Cache-Control"       content="no-cache" />
<meta http-equiv="Expires"             content="Thu, 01 Dec 1994 16:00:00 GMT" />
<meta http-equiv="content-type"        content="text/html;charset=UTF-8" />
<meta http-equiv="content-language"    content="ja" />
<meta http-equiv="content-style-type"  content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<title>お問い合わせ ～追加データ設定画面～</title>
</head>

<body>
<h2>お問い合わせ ～追加データ設定画面 ver.{$version_setting}～　(system ver.{$version})</h2>
<form method="post" name="frm" action="">
<input type="hidden" name="act" value="UPD">
<input type="hidden" name="col" value="{$cnt_col}">
{$html}
<input type="submit" value="更新" style="width:200px">
</form>
</body>
</html>

END;
unset($html);
?>