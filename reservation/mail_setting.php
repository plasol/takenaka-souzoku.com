<?php
//------------------------------------------------------------------------------
// システム名：問い合わせフォーム
// 機能名　　：問い合わせ項目設定
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
// 初期設定
//--------------------------------------------------
// 共通
$nowDate = date("YmdHis");      // サーバー時刻取得
$version = VERSION_MAIL;
$version_setting = "2.2.3";

$lst_data = getMailData(FILE_DATA_MAIN);

//--------------------------------------------------
// POST
//--------------------------------------------------
$item1   = $_POST["item1"];    // 番号
$item2   = $_POST["item2"];    // 項目名
$item3   = $_POST["item3"];    // HTMLタイプ
$item4   = $_POST["item4"];    // DATAタイプ
$item5   = $_POST["item5"];    // 文字数
$item6   = $_POST["item6"];    // テキストサイズ
$item7   = $_POST["item7"];    // 必須入力
$item8   = $_POST["item8"];    // class名
$item9   = $_POST["item9"];    // オプション
$item10  = $_POST["item10"];   // 初期値
$item11  = $_POST["item11"];   // タグ前テキスト
$item12  = $_POST["item12"];   // タグ後テキスト
$item13  = $_POST["item13"];   // 次項目連結
$item14  = $_POST["item14"];   // 読込みファイル
$item15  = $_POST["item15"];   // 任意のチェックエラー文字列
$item16  = $_POST["item16"];   // 確認用連携項目番号
$reqidx  = $_POST["reqidx"];   // インデックス

$act = $_POST["act"];
if($act != "") {
	//--------------------------------------------------
	// 更新・削除
	//--------------------------------------------------
	$res = "";
	$msg = "";

	if($act != "DEL") {
		//--------------------------------------------------
		// 入力チェック
		//--------------------------------------------------

		// 番号
		if($res === "" && $item1 == "") {
			$res = "番号" . MSG_NULL;
		}
		if($res === "" && !is_numeric($item1)) {
			$res = "番号" . MSG_NUM;
		}
		if($res != "") { $msg .= "<br>" . $res; $res = ""; }

		// HTMLタイプ
		if($res === "" && $item3 == "") {
			$res = "HTMLタイプ" . MSG_NULL;
		}
		if($res != "") { $msg .= "<br>" . $res; $res = ""; }

		// 文字数
		if($res === "" && $item5 != "" && !is_numeric($item5)) {
			$res = "文字数" . MSG_NUM;
		}
		if($res != "") { $msg .= "<br>" . $res; $res = ""; }

		// テキストサイズ
		if($res === "" && $item6 != "" && !is_numeric($item6)) {
			$res = "テキストサイズ" . MSG_NUM;
		}
		if($res != "") { $msg .= "<br>" . $res; $res = ""; }

		// 必須入力
		if($res === "" && $item7 == "") {
			$res = "必須入力" . MSG_NULL;
		}
		if($res != "") { $msg .= "<br>" . $res; $res = ""; }
	}

	if($msg == "") {
		//--------------------------------------------------
		// 削除
		//--------------------------------------------------
		unset($lst_data[$reqidx]);

		//--------------------------------------------------
		// 登録
		//--------------------------------------------------
		if($act == "UPD") {

			$item14 = str_replace("\r\n", CUT_COL, $item14);
			$item14 = str_replace("\r", CUT_COL, $item14);
			$item14 = str_replace("\n", CUT_COL, $item14);

			$lst_data[$reqidx][0]  = $item1;
			$lst_data[$reqidx][1]  = $item2;
			$lst_data[$reqidx][2]  = $item3;
			$lst_data[$reqidx][3]  = $item4;
			$lst_data[$reqidx][4]  = $item5;
			$lst_data[$reqidx][5]  = $item6;
			$lst_data[$reqidx][6]  = $item7;
			$lst_data[$reqidx][7]  = $item8;
			$lst_data[$reqidx][8]  = $item9;
			$lst_data[$reqidx][9]  = $item10;
			$lst_data[$reqidx][10] = $item11;
			$lst_data[$reqidx][11] = $item12;
			$lst_data[$reqidx][12] = $item13;
			$lst_data[$reqidx][13] = $item14;
			$lst_data[$reqidx][14] = $item15;
			$lst_data[$reqidx][15] = $item16;
		}
		sort($lst_data);

		setMailData(FILE_DATA_MAIN, $lst_data);

		// 初期化
		$item1  = "";
		$item2  = "";
		$item3  = "";
		$item4  = "";
		$item5  = "";
		$item6  = "";
		$item7  = "";
		$item8  = "";
		$item9  = "";
		$item10 = "";
		$item11 = "";
		$item12 = "";
		$item13 = "";
		$item14 = "";
		$item15 = "";
		$item16 = "";
	}
}

//--------------------------------------------------
// HTMLタイプ
//--------------------------------------------------
$lstItem3 = array();
$lstItem3["TEXT"]      = "テキスト";
$lstItem3["TEXT_AREA"] = "複数行テキスト";
$lstItem3["PASSWORD"]  = "パスワード";
$lstItem3["SELECT"]    = "プルダウン";
$lstItem3["RADIO"]     = "ラジオボタン";
$lstItem3["CHECK"]     = "チェックボックス";
$inItem3 = getSelectBox("item3", $item3, "onchange=\"fncLock(this);\"", $lstItem3, true);

//--------------------------------------------------
// DATAタイプ
//--------------------------------------------------
$lstItem4 = array();
$lstItem4["NONE"]     = "設定なし";
$lstItem4["NUMERIC"]  = "数値";
$lstItem4["ASCII"]    = "半角英数字";
$lstItem4["ALPHABET"] = "アルファべット";
$lstItem4["ZEN"]      = "全角";
$lstItem4["KANA_ZEN"] = "全角カタカナ";
$lstItem4["CURRENCY"] = "通貨";
$lstItem4["MAIL"]     = "メールアドレス";
$lstItem4["PHONE"]    = "電話番号";
$lstItem4["ZIP"]      = "郵便番号";
$lstItem4["DATE6"]    = "日付(yyyyMM)";
$lstItem4["DATE"]     = "日付(yyyyMMdd)";
$lstItem4["TIME4"]    = "時間(HHmm)";
$lstItem4["TIME6"]    = "時間(HHmmss)";
$lstItem4["TIME14"]   = "日付(yyyyMMddHHmmss)";
$lstItem4["TIME5"]    = "時間(HH:mm)";
$lstItem4["TIME7"]    = "時間(HH:mm:ss)";
$inItem4 = getSelectBox("item4", $item4, "", $lstItem4);

//--------------------------------------------------
// 必須入力
//--------------------------------------------------
$lstItem7 = array();
$lstItem7["0"] = "空を許可";
$lstItem7["1"] = "必須入力";
$inItem7 = getSelectBox("item7", $item7, "", $lstItem7);

//--------------------------------------------------
// 次項目連結
//--------------------------------------------------
$lstItem13 = array();
$lstItem13["0"] = "";
$lstItem13["1"] = "連結";
$inItem13 = getSelectBox("item13", $item13, "", $lstItem13);

//--------------------------------------------------
// メール設定内容取得
//--------------------------------------------------
$idx = 0;
$tag = "";

while(is_array($lst_data) && list($key, $val) = each($lst_data))
{
	$mouse = "";
	$mouse .= " onMouseOver=\"document.getElementById('row_1$idx').style.backgroundColor = '#f0f8ff';document.getElementById('row_2$idx').style.backgroundColor = '#f0f8ff';\"";
	$mouse .= " onMouseOut=\"document.getElementById('row_1$idx').style.backgroundColor  = '#FFFFFF';document.getElementById('row_2$idx').style.backgroundColor  = '#FFFFFF';\"";
	$mouse .= " style='cursor:hand;'";
	$mouse .= " onclick='view($idx);'";

	$val[13] = str_replace(CUT_COL, "<br />\n", $val[13]);

	$tag .= "<tr bgcolor='#FFFFFF' id='row_1$idx' $mouse>\n";
	$tag .= "<td id='item1_$idx'  width='42' align='center'>$val[0]</td>\n";
	$tag .= "<td id='item2_$idx'  width='80'>$val[1]</td>\n";
	$tag .= "<td width='170'><span id='item3_$idx' style='display:none;'>" . $val[2] . "</span>" . $lstItem3[$val[2]] . "<br />\n";
	$tag .= "<span id='item4_$idx' style='display:none;'>" . $val[3] . "</span>" . $lstItem4[$val[3]] . "</td>\n";
	$tag .= "<td id='item5_$idx'  width='45'>$val[4]</td>\n";
	$tag .= "<td id='item6_$idx'  width='65'>$val[5]</td>\n";
	$tag .= "<td width='85'><span id='item7_$idx' style='display:none;'>" . $val[6] . "</span>" . $lstItem7[$val[6]] . "</td>\n";
	$tag .= "<td id='item8_$idx'  width='60'>$val[7]</td>\n";
	$tag .= "<td id='item9_$idx'  width='70'>$val[8]</td>\n";
	$tag .= "<td id='item10_$idx' width='75'>$val[9]</td>\n";
	$tag .= "<td id='item11_$idx' width='60'>$val[10]</td>\n";
	$tag .= "<td id='item12_$idx' width='60'>$val[11]</td>\n";
	$tag .= "<td width='100'><span id='item13_$idx' style='display:none;'>" . $val[12] . "</span>" . $lstItem13[$val[12]] . "<br />\n";
	$tag .= "<span id='item16_$idx'>$val[15]</span></td>\n";
	$tag .= "<td id='item14_$idx' width='160'><div style='width:100px;height:40px;overflow:hidden;'>$val[13]</div></td>\n";
	$tag .= "</tr>\n";
	$tag .= "<tr bgcolor='#FFFFFF' id='row_2$idx' $mouse>\n";
	$tag .= "<td id='item15_$idx' colspan='13'><div style='width:1100px;height:40px;overflow:hidden;'>$val[14]</div></td>\n";
	$tag .= "</tr>\n";

	$idx++;
}

$html = fncGetFile("./", "mail_setting.html");
$html = str_replace('&&&VERSION_SETTING&&&', $version_setting, $html);
$html = str_replace('&&&VERSION&&&', $version, $html);
$html = str_replace('&&&MESSAGE&&&', $msg, $html);
$html = str_replace('&&&TAGS&&&', $tag, $html);
for ($i = 1;$i <= 16;$i++) {
	$nm = "item{$i}";
	if (${$nm} == "") { $nm = "inItem{$i}"; }
	$html = str_replace("&&&ITEM{$i}&&&", ${$nm}, $html);
}
echo $html;
