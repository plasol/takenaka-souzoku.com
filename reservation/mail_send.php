<?php
//------------------------------------------------------------------------------
// システム名：問い合わせフォーム
// 機能名　　：メール送信
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
$nowDate = date("YmdHis");      // サーバー時刻取得
$sep_mail = STRING_REPLACE_SEPARATOR_MAIL; // 区切り文字（問い合わせ）

$msg_err = "";
$sess_items      =& $_SESSION[SESSION_MAIL][NM_CTL];
$sess_items_etc  =& $_SESSION[SESSION_MAIL][NM_CTL_ETC];
$sess_items_post =& $_SESSION[SESSION_MAIL][NM_CTL_POST];
$send_time       =& $_SESSION[SESSION_MAIL][SEND_TIME];

$make_time = $_POST["t"];
$flg_back  = $_POST["b"]; // 戻り処理（セッション内容を更新しない）
$_POST["b"] = '';

// ■処理区分
// INPUT   ＝初期表示・エラー時・別画面入力時
// CONFIRM ＝確認画面（入力内容確定）
// SEND_END＝送信完了画面（送信処理）
$kbn_send = "";
if($_GET["s"] != "") {
	// 暗号化解除
	$kbn_send = base64_decode($_GET["s"]);
}

//--------------------------------------------------
// 問い合わせ項目取得
//--------------------------------------------------
$lst_colmns = getMailData(FILE_DATA_MAIN);

//--------------------------------------------------
// 各項目の取得
//--------------------------------------------------
if($kbn_send == "") {
	// 初期表示

	// セッションクリア
	$sess_items = "";
	$sess_items_etc = "";
	$sess_items_post = "";

	// 拡張データID設定
	$data_id = $_POST['data_id'];
} else {
	// 画面遷移後
	if($kbn_send == "INPUT" && !$flg_back || $kbn_send == "CONFIRM") {
		$lst = $lst_colmns;
	 	while(is_array($lst) && list(, $val) = each($lst)) {
			// セッションに保持（上書きする）
			$sess_items[$val[0]] = $_POST[NM_CTL . $val[0]];
		}
		unset($lst);
	}

	// 拡張データID取得
	$data_id = $sess_items_etc['data_id'];
}

//--------------------------------------------------
// 異常処理
//--------------------------------------------------
// 異常処理（拡張データがない場合、リダイレクト）
if (CHECK_SESSION_ETC && !$data_id) {
	// セッションクリア
	$sess_items = "";
	$sess_items_etc = "";
	$sess_items_post = "";
	unset($sess_items);
	unset($sess_items_etc);
	unset($sess_items_post);

	relative_redirect(BACK_URL);
	exit();
}
// 不正な処理（確認画面）
if ("" != $kbn_send && ($make_time < $send_time || "" == $make_time)) {
	// セッションクリア
	$sess_items = "";
	$sess_items_etc = "";
	$sess_items_post = "";
	unset($sess_items);
	unset($sess_items_etc);
	unset($sess_items_post);

	relative_redirect(BACK_URL);
	exit();
}

//--------------------------------------------------
// 追加データ項目の取得
//--------------------------------------------------
// 追加データのIDをセッションに保存
$sess_items_etc['data_id'] = $data_id;

$result      = getExData($data_id);
$lst_ex_data = $result['lst_ex_data'];
$lst_mail    = $result['lst_mail'];
unset($result);

// POST置換データより置換処理を行なう
while (is_array($_POST) && list($key, $val) = each($_POST)) {
	// 列（キーを基に、値を置換する）
	if (is_array($val)) { continue; }
	if (mb_strpos($key, "{$sep_mail}") !== false) {
		// 置換文字列を含む項目
		$sess_items_post[$key] = $val;
	}
}

//--------------------------------------------------
// チェック処理（確認画面）
//--------------------------------------------------
if($kbn_send == CONFIRM) {
	//--------------------------------------------------
	// 入力チェック
	//--------------------------------------------------
	$msg_err .= checkType($lst_colmns, $sess_items);
}

//--------------------------------------------------
// 初期時またはエラー発生時は入力画面に戻す
//--------------------------------------------------
if($kbn_send == "" || $msg_err != "") {
	$kbn_send = INPUT;
}

//--------------------------------------------------
// 処理の分岐
//--------------------------------------------------
switch($kbn_send) 
{
	case INPUT:
		//--------------------------------
		// 入力画面
		//--------------------------------
		// テンプレートファイルの読込み
		$template_input = FILE_INPUT;
		if($_POST["next_page"] != "") {
			// 別入力画面時
			$template_input = $_POST["next_page"];
		}
		$get_html = fncGetFile(PATH_TMP, $template_input);
		// 作成した時間を入れる
		$get_html = str_replace("{$sep_mail}TIME{$sep_mail}",  $nowDate, $get_html);

		// エラーメッセージ置換
		$get_html = str_replace("{$sep_mail}MSG_ERR{$sep_mail}", $msg_err, $get_html);

		// HTML編成
		setFormRelace("0", $get_html, $lst_colmns, $sess_items);

		//--------------------------------
		// 次画面パス置換
		//--------------------------------
		if($_POST["next_page"] == "") {
			$get_html = str_replace("{$sep_mail}SEND{$sep_mail}",  MY_FILE . base64_encode(CONFIRM), $get_html);
		} else {
			$get_html = str_replace("{$sep_mail}SEND{$sep_mail}",  MY_FILE . base64_encode(INPUT), $get_html);
		}
		break;

	case CONFIRM:

		//--------------------------------
		// 確認画面
		//--------------------------------
		// テンプレートファイルの読込み
		$get_html = fncGetFile(PATH_TMP, FILE_CONFIRM);
		// 作成した時間を入れる
		$get_html = str_replace("{$sep_mail}TIME{$sep_mail}",  $nowDate, $get_html);

		// HTML編成
		setFormRelace("1", $get_html, $lst_colmns, $sess_items);

		//--------------------------------
		// 次画面パス置換
		//--------------------------------
		$get_html = str_replace("{$sep_mail}SEND{$sep_mail}",  MY_FILE . base64_encode(SEND_END), $get_html);
		$get_html = str_replace("{$sep_mail}BACK{$sep_mail}",  MY_FILE . base64_encode(INPUT), $get_html);
		break;

	case SEND_END:
		//--------------------------------
		// メール送信と終了確認画面
		//--------------------------------
		// テンプレートファイルの読込み
		$get_html = fncGetFile(PATH_TMP, FILE_END);
		// 作成した時間を入れる
		$get_html = str_replace("{$sep_mail}TIME{$sep_mail}",  $nowDate, $get_html);

		// メール送信
		setFormRelace("2", $get_html, $lst_colmns, $sess_items, $lst_mail);

		// HTML編成
		setFormRelace("1", $get_html, $lst_colmns, $sess_items);

		// セッションクリア
		$sess_items = "";
		$sess_items_etc = "";
		$sess_items_post = "";
		unset($sess_items);
		unset($sess_items_etc);
		unset($sess_items_post);

		// 送信済みに設定する。
		$send_time = $nowDate;
		break;

	default:
		break;
}

//--------------------------------
// 置換処理
//--------------------------------
// 追加データより置換処理を行なう
while (is_array($lst_ex_data) && list(, $row) = each($lst_ex_data)) {
	// 行
	while (is_array($row) && list($key, $val) = each($row)) {
		// 列（キーを基に、値を置換する）
		if (is_array($val)) { continue; }
		if (mb_strpos($key, "{$sep_mail}") !== false) {
			// 置換文字列を含む項目
			$val = htmlspecialchars($val, ENT_QUOTES);
			$get_html = str_replace($key,  $val, $get_html);
		}
	}
}
// POST置換データより置換処理を行なう
while (is_array($sess_items_post) && list($key, $val) = each($sess_items_post)) {
	// 列（キーを基に、値を置換する）
	if (is_array($val)) { continue; }
	if (mb_strpos($key, "{$sep_mail}") !== false) {
		// 置換文字列を含む項目
		$val = htmlspecialchars($val, ENT_QUOTES);
		$get_html = str_replace($key,  $val, $get_html);
	}
}

//--------------------------------
// 結果HTMLの吐き出し
//--------------------------------
if (!headers_sent()) {
	header("Content-Type: text/html; charset=UTF-8");
	header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
	header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}
echo $get_html;
