<?php
//------------------------------------------------------------------------------
// システム名：問い合わせフォーム
// 機能名　　：共通関数
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2010. PLASOL Inc. All Right Reserved.
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
// 画面を表示する
// 引数：$html = HTML文
//------------------------------------------------
function printHtml($html)
{
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
	echo $html;
}

//------------------------------------------------
// 画面を表示する
//------------------------------------------------
function printErrorPage()
{
	// セッションクリア
	$sess_items = "";
	$sess_items_etc = "";
	$sess_items_post = "";
	unset($sess_items);
	unset($sess_items_etc);
	unset($sess_items_post);

	// テンプレート取得
	$html = fncGetFile(PATH_TMP, FILE_ERROR);

	// 区切り文字（問い合わせ）
	$sep_mail = STRING_REPLACE_SEPARATOR_MAIL;

	// 作成した時間を入れる
	$html = str_replace("{$sep_mail}BACK_URL{$sep_mail}",  BACK_URL, $html);

	printHtml($html);
}

//------------------------------------------------
// MAILフォームデータを保存します。
// 引数：$nm_file = ファイル名
// 引数：$lst     = 保管情報
//------------------------------------------------
function setMailData($nm_file, $lst) 
{
	$path = PATH_DATA_DIR . $nm_file;

	$str_save = "";
	while(is_array($lst) && list($key, $val) = each($lst)) {
		$i=0;
		if($str_save != "") { $str_save .= "\n"; }

		while(is_array($val) && list($key_1, $val_1) = each($val)) {
			if($i==1) { $str_save .= CUT_COL_DEF; }
			$str_save .= $val_1;
			$i=1;
		}
	}

	// 書き込み
	$fp = fopen($path, "w");
	fwrite($fp, $str_save);
	fclose($fp);
}
//------------------------------------------------
// MAILフォームデータを出力します。
// 引数：$nm_file = ファイル名
//------------------------------------------------
function getMailData($nm_file) 
{
	$str = fncGetFile(PATH_DATA_DIR, $nm_file);

	if($str != "") {
		$lst = explode("\n", $str);
	}

	$lst_return = array();
	while(is_array($lst) && list($key, $val) = each($lst)) {
		$lst_return[$key] = explode(CUT_COL_DEF, $val);
	}

	return $lst_return;
}
//------------------------------------------------
// ファイルを取得
// 引数：$path = ファイルパス
// 　　　$file = ファイル名
//------------------------------------------------
function fncGetFile($path, $file) 
{
	$str = "";
	$is_file = $path . $file;
	if(is_file($is_file)) {

		$fp = fopen ($is_file, "r");

		while (!feof($fp)) {
			$str = fread ($fp, filesize($is_file) + 1);
		}
		fclose($fp);
	}
	return $str;
}

//--------------------------------------------------
// 追加データ項目の取得
//--------------------------------------------------
function getExData($data_id = NULL)
{
	// 追加データ配列
	$lst_ex_data = NULL;

	// メールアドレス用配列
	$lst_mail = NULL;

	// データ読込
	$lst = getMailData(FILE_DATA_EX);
	if ($lst) {
		// 追加データあり
		$lst_ex_data = array();

		// 項目定義取得
		$lst_header = array_flip($lst[0]); // キーと値を反転 0->mail → mail->0

		// メールアドレス用配列
		$def_mail = $lst_header['mail'];
		if (isset($lst_header['mail'])) {
			// メールアドレス用配列あり
			$lst_mail = array();
		}

		if ($data_id) {
			// ID指定ありの場合、その行のみ取得
			$cols = $lst[$data_id];

			// 配列再作成
			$lst = array();
			$lst[] = $cols;
		} else {
			// ヘッダー行除去
			array_shift($lst);
		}

		for ($i = 0;$i < count($lst);$i++) {
			// 行取得
			$cols = $lst[$i];

			// 列展開
			while (list($key, $col_no) = each($lst_header)) {
				// 項目数分の列を作成
				$val = $cols[$col_no]; // 同じ列番号の値を取得
				$lst_ex_data[$i][$key] = $val; // 追加データ配列に追加
				if ($col_no === $def_mail) {
					// メールアドレス追加
					$lst_mail[] = $val;
				}
			}
			reset($lst_header);
		}
	}

	$result = array();
	$result['lst_header']  = $lst_header;
	$result['lst_ex_data'] = $lst_ex_data;
	$result['lst_mail']    = $lst_mail;
	return $result;
}

//--------------------------------------------------
// HTMLデータ変換関数
//--------------------------------------------------
function convert_data($val)
{
	// HTML文字列に変換
	$val = htmlspecialchars($val, ENT_QUOTES);
	// 置換文字列は許可
	#$val = str_replace("&amp;&amp;&amp;", "&&&", $val);
	return $val;
}

//--------------------------------------------------
// メールデータ変換関数
//--------------------------------------------------
function convert_mail_data($val)
{
	if (ENCODING_SEND_MAIL) {
		$val = mb_convert_encoding($val, "JIS", mb_internal_encoding());
	}
	return $val;
}

//--------------------------------------------------
// フォーム生成関数
// 引数　：kbn_field  = 0→入力，1→確認，2→メール送信
// 　　　　str_html   = 対象HTML文字列（参照渡し）
// 　　　　set_colmns = 変換対象項目
// 　　　　set_value  = POSTで取得した項目値
// 　　　　lst_mail   = （送信時のみ）追加アドレスリスト
//--------------------------------------------------
function setFormRelace($kbn_field, &$str_html, $set_colmns, $set_value, $lst_mail = '')
{
	$sep_mail = STRING_REPLACE_SEPARATOR_MAIL; // 区切り文字（問い合わせ）

	$i = 0;
	$str_body = "";
	$lst = $set_colmns;
	$con = '';
	$str_save = "";

	if (USE_SEND_MAIL_TEMPLATE) {
		$str_body = fncGetFile(PATH_TMP, SEND_MAIL_TEMPLATE);
	}

	// 管理者件名テンプレート取得
	$owner_subject = OWNER_SUBJECT;

	while(is_array($lst) && list(, $val) = each($lst)) 
	{
		//------------------------------------------------
		// 画面表示項目取得
		//------------------------------------------------
		$cnt = 0;
		$no    = $val[$cnt++];   //  0: 番号
		$nm    = $val[$cnt++];   //  1: 項目名
		$html  = $val[$cnt++];   //  2: HTMLタイプ
		$type  = $val[$cnt++];   //  3: DATAタイプ
		$len   = $val[$cnt++];   //  4: 文字数
		$size  = $val[$cnt++];   //  5: テキストサイズ
		$null  = $val[$cnt++];   //  6: 必須入力
		$css   = $val[$cnt++];   //  7: スタイル
		$opt   = $val[$cnt++];   //  8: オプション
		$def   = $val[$cnt++];   //  9: 初期値
		$back  = $val[$cnt++];   // 10: タグ前テキスト
		$forth = $val[$cnt++];   // 11: タグ後テキスト
		$con_n = $val[$cnt++];   // 12: 次項目連結
		$data  = $val[$cnt++];   // 13: 読込みファイル
		$msg   = $val[$cnt++];   // 14: 優先メッセージ
		$chk_n = $val[$cnt++];   // 15: 確認用項目番号
		unset($val);
		unset($cnt);

		$value = "";
		if (isset($set_value[$no])) {
			$value = $set_value[$no]; // POST値
		}
		if($kbn_field == 2 && $value != "" && !is_array($value)) {
			// メール送信の場合、半角カナ→全角カナ
			$value = mb_convert_kana($value, "KV");
		}

		if($kbn_field == 0) {
			//------------------------------------------------
			// 入力
			//------------------------------------------------
			// コントロールの名前と値の設定
			$nm_item = NM_CTL . $no;         // フォームItem名

			if($value == "") { $value = $def; }

			//------------------------------------------------
			// 複数生成コントロール（checkbox, radio）
			//------------------------------------------------
			$lst_data = array();
			if($data != "") {
				$str = str_replace("\n", "", $data);
				$str = str_replace("\r", "", $str);
				$lst_data = explode(CUT_COL, $str);
			}

			//------------------------------------------------
			// 入力フォーム生成
			//------------------------------------------------
			// NAMEの置換
			$str_html = str_replace("{$sep_mail}NAME" . $no . "{$sep_mail}", $nm, $str_html);

			// VALIDATEの置換
			$validate = VALIDATE_ERROR_TEMPLATE;
			if ($html == "CHECK") {
				$validate = str_replace("{$sep_mail}ID{$sep_mail}", $nm_item . "[]", $validate);
			} else {
				$validate = str_replace("{$sep_mail}ID{$sep_mail}", $nm_item, $validate);
			}
			$str_html = str_replace("{$sep_mail}VALIDATE" . $no . "{$sep_mail}", $validate, $str_html);

			// TAGの置換
			switch($html) {
				case "TEXT":
					// HTML文字列に変換
					$value = convert_data($value);

					// パラメータ定義
					$ctl_id   = sprintf("id='%s'", $nm_item);
					$ctl_nm   = sprintf("name='%s'", $nm_item);
					$ctl_val  = sprintf("value='%s'", $value);
					$ctl_len  = sprintf("maxlength='%s'", $len);
					$ctl_size = sprintf("size='%s'", $size);
					$ctl_css  = sprintf("class='%s'", $css);
					$ctl_opt  = $opt;
					// テンプレート
					$tmp = "%s<input type='text' %s %s %s %s %s %s %s />%s";
					// 代入
					$control = sprintf($tmp, $back, $ctl_id, $ctl_nm, $ctl_val, $ctl_len, $ctl_size, $ctl_css, $ctl_opt, $forth);
					$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $control, $str_html);
					break;

				case "TEXT_AREA":
					// HTML文字列に変換
					$value = convert_data($value);

					// パラメータ定義
					$ctl_id   = sprintf("id='%s'", $nm_item);
					$ctl_nm   = sprintf("name='%s'", $nm_item);
					$ctl_cols = sprintf("cols='%s'", HTML_TEXTAREA_COLS);
					$ctl_rows = sprintf("rows='%s'", HTML_TEXTAREA_ROWS);
					$ctl_css  = sprintf("class='%s'", $css);
					$ctl_opt  = $opt;
					$ctl_val  = $value;
					
					// テンプレート
					$tmp = "%s<textarea %s %s %s %s %s %s wrap='hard'>%s</textarea>%s";
					// 代入
					$control = sprintf($tmp, $back, $ctl_id, $ctl_nm, $ctl_cols, $ctl_rows, $ctl_css, $ctl_opt, $ctl_val, $forth);
					$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $control, $str_html);
					break;

				case "PASSWORD":
					// HTML文字列に変換
					$value = convert_data($value);

					// パラメータ定義
					$ctl_id   = sprintf("id='%s'", $nm_item);
					$ctl_nm   = sprintf("name='%s'", $nm_item);
					//$ctl_val  = sprintf("value='%s'", $value);
					$ctl_val  = "";
					$ctl_len  = sprintf("maxlength='%s'", $len);
					$ctl_size = sprintf("size='%s'", $size);
					$ctl_css  = sprintf("class='%s'", $css);
					$ctl_opt  = $opt;
					// テンプレート
					$tmp = "%s<input type='password' %s %s %s %s %s %s %s />%s";
					// 代入
					$control = sprintf($tmp, $back, $ctl_id, $ctl_nm, $ctl_val, $ctl_len, $ctl_size, $ctl_css, $ctl_opt, $forth);
					$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $control, $str_html);
					break;

				case "SELECT":
					// 自動生成
					$ctl_id  = sprintf("id='%s'", $nm_item);
					$control = sprintf("%s%s%s", $back, getHtmlSelect($data, $nm_item, $value, $ctl_id . $opt, true), $forth);
					$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $control, $str_html);
					break;

				case "RADIO":
					$idx = 1;
					for ($idx = 1;is_array($lst_data) && list(, $val_1) = each($lst_data);$idx++) {
						// 選択状態可否
						$chk = "";
						if($value == $val_1) { $chk = "checked"; }
						// パラメータ定義
						$ctl_id   = sprintf("id='%s'", $nm_item);
						$ctl_nm   = sprintf("name='%s'", $nm_item);
						$ctl_val  = sprintf("value='%s'", $val_1);
						$ctl_css  = sprintf("class='%s'", $css);
						$ctl_opt  = $opt;
						// テンプレート
						$tmp = "%s<input type='radio' %s %s %s %s %s %s />%s%s";
						// 代入
						$control = sprintf($tmp, $back, $ctl_id, $ctl_nm, $ctl_val, $ctl_css, $ctl_opt, $chk, $val_1, $forth);
						$str_html = str_replace("{$sep_mail}TAG" . $no . "_" . $idx . "{$sep_mail}",  $control, $str_html);
					}
					break;

				case "CHECK":
					for ($idx = 1;is_array($lst_data) && list(, $val_1) = each($lst_data);$idx++) {
						// 選択状態可否
						$chk = "";
						for($j=0;$j<count($value);$j++) {
							if($value[$j] == $val_1) { $chk = "checked"; }
						}
						// パラメータ定義
						$ctl_id   = sprintf("id='%s[]'", $nm_item);
						$ctl_nm   = sprintf("name='%s[]'", $nm_item);
						$ctl_val  = sprintf("value='%s'", $val_1);
						$ctl_css  = sprintf("class='%s'", $css);
						$ctl_opt  = $opt;
						// テンプレート
						$tmp = "%s<input type='checkbox' %s %s %s %s %s %s />%s%s";
						// 代入
						$control = sprintf($tmp, $back, $ctl_id, $ctl_nm, $ctl_val, $ctl_css, $ctl_opt, $chk, $val_1, $forth);
						$str_html = str_replace("{$sep_mail}TAG" . $no . "_" . $idx . "{$sep_mail}",  $control, $str_html);
					}
					break;
			}
		} else if($kbn_field == 1) {
			//------------------------------------------------
			// 確認
			//------------------------------------------------

			// 配列時、チェックボックス判断
			$vals = "";
			if(is_array($value)) {
				$in = false;
				$tmp = $value;
				while(list(, $val_1) = each($tmp)) {
					if($in) { 
						$vals .= SEPARATOR_DISP; 
					}
					$val_1 = htmlspecialchars($val_1, ENT_QUOTES);
					$vals .= $back . $val_1 . $forth;
					$in = true;
				}
			} else {
				// 通常
				$value = htmlspecialchars($value, ENT_QUOTES);
				if($value!=""){
					$vals = $back . getFormatString($value, STR_ENC_SYS_TO_HTML) . $forth;
				}else{
					$vals = getFormatString($value, STR_ENC_SYS_TO_HTML);
				}
			}
			$str_html = str_replace("{$sep_mail}NAME" . $no . "{$sep_mail}", $nm, $str_html);
			$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $vals, $str_html);
		} else if($kbn_field == 2) {
			//------------------------------------------------
			// メール送信
			//------------------------------------------------
			$i++;

			// メールアドレス取得
			if(TO_CSV_COL != "" && TO_CSV_COL != 0) {
				if($i == TO_CSV_COL) {
					$e_mail = $value;
				}
			}

			// メールアドレス取得
			//if(CHK_TO_CSV_COL != "" && CHK_TO_CSV_COL != 0) {
			//	if($i == CHK_TO_CSV_COL) {
			//		continue;
			//	}
			//}
			if ($chk_n == "end") { continue; }
			if ($html == "PASSWORD") { $value = "*****"; }

			// 配列時、チェックボックス判断
			$vals = "";
			if(is_array($value)) {
				$in = false;
				$tmp = $value;
				while(list(, $val_1) = each($tmp)) {
					if($in) { 
						$vals .= SEPARATOR_DISP_MAIL;
					}
					$vals .= $val_1;
					$in = true;
				}
			} else {
				if($value!=""){
					$vals = $back . $value . $forth;
				}else{
					$vals = "";
				}
			}

			// メール項目追加
			if (USE_SEND_MAIL_TEMPLATE) {
				// テンプレート置換処理
				$str_body = str_replace("{$sep_mail}NAME{$no}{$sep_mail}", $nm, $str_body);
				$str_body = str_replace("{$sep_mail}VALUE{$no}{$sep_mail}", $vals, $str_body);
			} else {
				// 項目レイアウト追加
				if ($con) {
					// 項目連結の場合、値と区切り文字
					$str_body .= SEPARATOR_DISP_CON . $vals;
				} else {
					// 通常項目
					$str_send_mail_layout = SEND_MAIL_LAYOUT;
					$str_send_mail_layout = str_replace("MAIL_ITEM_NAME", $nm, $str_send_mail_layout);
					$str_send_mail_layout = str_replace("MAIL_ITEM_VALUE", $vals, $str_send_mail_layout);
					$str_body .= $str_send_mail_layout;
				}
				// 次項目連携がない場合、改行
				if (!$con_n) {
					$str_body .= "\n";
				}
			}

			// 件名に値出力
			$owner_subject = str_replace("{$sep_mail}NAME{$no}{$sep_mail}", $nm, $owner_subject);
			$owner_subject = str_replace("{$sep_mail}VALUE{$no}{$sep_mail}", $vals, $owner_subject);
		} else if($kbn_field == 3) {
			//------------------------------------------------
			// CSV出力
			//------------------------------------------------
			// 配列時、チェックボックス判断
			$vals = "";
			if(is_array($value)) {
				$in = false;
				$tmp = $value;
				while(list(, $val_1) = each($tmp)) {
					if($in) { 
						$vals .= SEPARATOR_DISP_MAIL;
					}
					$vals .= $val_1;
					$in = true;
				}
			} else {
				if($value!=""){
					$vals = $back . $value . $forth;
				}else{
					$vals = "";
				}
			}

			$str_save .= "\"" . $vals . "\",";
		}
		$con = $con_n;
	}

	if($kbn_field == 2) {
		$from = FROM;

		$guest_body = convert_mail_data(GUEST_HEADER . $str_body . GUEST_FOOTER);
		$guest_subject = convert_mail_data(GUEST_SUBJECT);
		$multi_body = convert_mail_data(MULTI_HEADER . $str_body . MULTI_FOOTER);
		$multi_subject = convert_mail_data(MULTI_SUBJECT);
		$owner_body = $str_body;
		if (USE_OUT_CLIENT_INFO) {
			// 接続端末情報付加（管理者メール）
			$ip = getIP();
			$host = gethostbyaddr($ip);
			$browser = $_SERVER['HTTP_USER_AGENT'];

			$str_send_mail_layout = SEND_MAIL_LAYOUT_CLIENT_INFO;
			$str_send_mail_layout = str_replace("MAIL_ITEM_IP", $ip, $str_send_mail_layout);
			$str_send_mail_layout = str_replace("MAIL_ITEM_HOST", $host, $str_send_mail_layout);
			$str_send_mail_layout = str_replace("MAIL_ITEM_BROWSER", $browser, $str_send_mail_layout);
			$owner_body .= $str_send_mail_layout;
		}
		$owner_body = convert_mail_data(OWNER_HEADER . $owner_body . OWNER_FOOTER);
		$owner_subject = convert_mail_data($owner_subject);

		if (ST_SEND_PATTERN == 1) {
			// ▼通常
			// ※管理者アドレスが未設定の場合、メールは「全て送信されません」
			// ※追加アドレスが設定されている（ex.datが存在する）場合、３．が実行されます。

			// １．管理者 → 一般ユーザー　　　　　　　　※一般ユーザーが未設定の場合、メールは「送信されません」
			// ２．一般ユーザー → 管理者　　　　　　　　※一般ユーザーが未設定の場合、管理者として「送信する」
			// ３．管理者 → 追加アドレス（複数）　　　　※追加アドレス（複数）が未設定の場合、メールは「送信されません」

			if(FROM == "") { return false; }

			if($e_mail != "") {
				// １．管理者 → 一般ユーザー
				sendMail($e_mail, $guest_subject, $guest_body, $from);

				// ２．一般ユーザー → 管理者
				sendMail($from, $owner_subject, $owner_body, $e_mail);
			} else {

				// ２．管理者 → 管理者
				sendMail($from, $owner_subject, $owner_body, $from);
			}

			// 追加アドレスを一括送信
			// ３．管理者 → 追加アドレス（複数）
			if ($lst_mail != "") {
				if (!is_array($lst_mail)) {
					// 追加アドレスを配列化する
					$tmp = $lst_mail;
					$lst_mail = array();
					$lst_mail[] = $tmp;
				}
				while(list(, $ex_add) = each($lst_mail)) {
					sendMail($ex_add, $multi_subject, $multi_body, $from);
				}
			}
		} else if (ST_SEND_PATTERN == 2) {
			// ▼追加アドレス優先
			// ※追加アドレスが未設定の場合、メールは「全て送信されません」

			// １．追加アドレス（複数） → 一般ユーザー　※一般ユーザーが未設定の場合、メールは「送信されません」
			// ２．追加アドレス（複数） → 管理者　　　　※管理者が未設定の場合、メールは「送信されません」
			// ３．一般ユーザー → 追加アドレス（複数）　※一般ユーザーが未設定の場合、メールは「送信されません」

			if($lst_mail == "") { return false; }

			if (!is_array($lst_mail)) {
				// 追加アドレスを配列化する
				$tmp = $lst_mail;
				$lst_mail = array();
				$lst_mail[] = $tmp;
			}

			while(list(, $ex_add) = each($lst_mail)) {
				if ($ex_add == '') { continue; }

				if($e_mail != "") {
					// １．追加アドレス（複数） → 一般ユーザー
					sendMail($e_mail, $guest_subject, $guest_body, $ex_add);
					// ３．一般ユーザー → 追加アドレス（複数）
					sendMail($ex_add, $multi_subject, $multi_body, $e_mail);
				}
				if($from != "") {
					// ２．追加アドレス（複数） → 管理者
					sendMail($from, $owner_subject, $owner_body, $ex_add);
				}
			}
		}
	} else if($kbn_field == 3) {
		//------------------------------------------------
		// データをCSVで保存
		//------------------------------------------------
		$str_save .= "\n";
		$path = PATH_DATA_DIR . FILE_DATA_OUT;

		// 書き込み
		$str_save = mb_convert_encoding($str_save , "sjis-win" , "UTF-8");
		$fp = fopen($path, "a");
		fwrite($fp, $str_save);
		fclose($fp);
	}
}

/**
 * メール送信
 * @param string $to 送信先
 * @param string $subject 件名
 * @param string $body 本文
 * @param string $from 送信元
 * @return array ユーザー情報
 */
function sendMail($to, $subject, $body, $from)
{
	// メールヘッダー
	$headers  = "From: {$from}" . "\n";
	$headers .= "Reply-To: {$from}" . "\n";
	$headers .= "MIME-Version: 1.0" . "\n";
	$headers .= "Date: " . date( "r") . "\n";

	$encode = 'UTF-8';
	if (MAIL_OLD_ENCODING == '1') {
		$encode = 'JIS';
		$headers .= "Content-type: text/plain; charset=iso-2022-jp";
	} else {
		$headers .= "Content-type: text/plain; charset={$encode}";
	}
	$body = mb_convert_encoding($body, $encode, "auto");    // 本文

	if (MAIL_BASE64 == '1') {
		$headers .= "\nContent-Transfer-Encoding: base64"."\n";
		$body    = base64_encode($body);
	}

	$encode = 'UTF-8';

	// mb_internal_encoding保管
	$encode_main = mb_internal_encoding();

	// 文字コードを一致させる（mb_internal_encoding = 件名の文字コード）
	mb_internal_encoding($encode);
	$subject = mb_convert_encoding($subject, $encode, mb_detect_encoding($subject));

	// 件名をMIME形式に変換
	$subject = mb_encode_mimeheader($subject, $encode);

	// mb_internal_encodingを元に戻す
	mb_internal_encoding($encode_main);

	// メール送信
	mail($to, $subject, $body, $headers);
}

//------------------------------------------------
// 自動生成項目用　文字数、型チェック関数
// 引数：$lstItem = 画面表示項目リスト
// 　　　$input   = 入力された内容
//------------------------------------------------
function checkType($lst_item, $lst_input)
{
	while(is_array($lst_item) && list(, $val) = each($lst_item))
	{
		//------------------------------------------------
		// 画面表示項目取得
		//------------------------------------------------
		$cnt = 0;
		$no    = $val[$cnt++];   //  0: 番号
		$nm    = $val[$cnt++];   //  1: 項目名
		$html  = $val[$cnt++];   //  2: HTMLタイプ
		$type  = $val[$cnt++];   //  3: DATAタイプ
		$len   = $val[$cnt++];   //  4: 文字数
		$size  = $val[$cnt++];   //  5: テキストサイズ
		$null  = $val[$cnt++];   //  6: 必須入力
		$css   = $val[$cnt++];   //  7: スタイル
		$opt   = $val[$cnt++];   //  8: オプション
		$def   = $val[$cnt++];   //  9: 初期値
		$back  = $val[$cnt++];   // 10: タグ前テキスト
		$forth = $val[$cnt++];   // 11: タグ後テキスト
		$con_n = $val[$cnt++];   // 12: 次項目連結
		$data  = $val[$cnt++];   // 13: 読込みファイル
		$msg   = $val[$cnt++];   // 14: 優先メッセージ
		$chk_n = $val[$cnt++];   // 15: 確認用項目番号
		unset($val);
		unset($cnt);

		// 入力値
		$value = "";
		if (isset($lst_input[$no])) {
			$value = $lst_input[$no];
		}

		if ($null == 1 && $value == "") {
			//------------------------------------------------
			// 必須入力 で 値が入力されていない場合
			//------------------------------------------------
			switch($html) {
				case "SELECT":
				case "RADIO":
				case "CHECK":
					if($msg == "") {
						$result .= $nm . " を選択してください。<br />";
					} else {
						$result .= $msg . "<br />";
					}
					break;
				default:
					if($msg == "") {
						$result .= $nm . " を入力してください。<br />";
					} else {
						$result .= $msg . "<br />";
					}
					break;
			}
		}

		if(is_array($value)) { continue; }

		//------------------------------------------------
		// 文字数チェック
		//------------------------------------------------
		if ($len >= 1 && $value != "") {
			$tmp = mb_convert_encoding($value, "EUC-JP");
			if (mb_strlen($tmp, "EUC-JP") > $len) {
				// 可変長　桁数超過
				$result .= $nm . " は" . $len . "文字以内で入力してください。<br />";
			}
		}

		if ($value != "") {
			//------------------------------------------------
			// 禁止文字入力チェック
			//------------------------------------------------
			if (PROHIBIT_CHECK && !checkProhibitString($value)) {
				$result .= $nm . " ". ERR_MSG_PROHIBIT;
			}

			//------------------------------------------------
			// 型チェック
			//------------------------------------------------
			if ($type == "NUMERIC") {
				//------------------------------------------------
				// タイプ＝数値
				//------------------------------------------------
				if (!is_numeric($value)) {
					$result .= $nm . " は数値で入力してください。<br />";
				}
			}
			if ($type == "ASCII") {
				//------------------------------------------------
				// タイプ＝ASCII
				//------------------------------------------------
				if (!isAscii($value)) {
					$result .= $nm . " は半角英数で入力してください。<br />";
				}
			}
			if ($type == "ALPHABET") {
				//------------------------------------------------
				// タイプ＝アルファベット
				//------------------------------------------------
				if (!isAlphabet($value)) {
					$result .= $nm . " は半角英字で入力してください。<br />";
				}
			}
			if ($type == "ZEN") {
				//------------------------------------------------
				// タイプ＝全角
				//------------------------------------------------
				// EUC-JPでなければチェックされない
				$value = mb_convert_encoding($value, "EUC-JP");

				if(!preg_match('/^([\xA1-\xFE][\xA1-\xFE])+$/', $value)){
					$result .= $nm . " は全角で入力してください。<br />";
				}
			}
			if ($type == "KANA_ZEN") {
				//------------------------------------------------
				// タイプ＝全角カナ
				//------------------------------------------------
				if (!isZenkakuKana($value)) {
					$result .= $nm . " は全角カナで入力してください。<br />";
				}
			}
			if ($type == "CURRENCY") {
				//------------------------------------------------
				// タイプ＝通貨
				//------------------------------------------------
				if (!isCurrency($value)) {
					$result .= $nm . " は通貨で入力してください。<br />";
				}
			}
			if ($type == "DECIMAL") {
				//------------------------------------------------
				// タイプ＝少数
				//------------------------------------------------
				if (!isDecimal($value)) {
					$result .= $nm . " は整数又は少数で入力してください。<br />";
				}
			}
			if ($type == "MAIL") {
				//------------------------------------------------
				// タイプ＝MAIL
				//------------------------------------------------
				if (!isMailAddress($value)) {
					$result .= $nm . " を正しく入力してください。<br />";
				}
			}
			if ($type == "PHONE") {
				//------------------------------------------------
				// タイプ＝電話番号
				//------------------------------------------------
				if (!isPhoneNumber($value)) {
					$result .= $nm . " を正しく入力してください。<br />";
				}
			}
			if ($type == "ZIP") {
				//------------------------------------------------
				// タイプ＝郵便番号
				//------------------------------------------------
				if (!isZip($value)) {
					$result .= $nm . " を正しく入力してください。<br />";
				}
			}
			if ($type == "DATE6") {
				//------------------------------------------------
				// タイプ＝年月 (yyyyMM)
				//------------------------------------------------
				if (!isDate6($value)) {
					$result .= $nm . " は日付書式(yyyyMM)で入力してください。<br />";
				}
			}
			if ($type == "DATE") {
				//------------------------------------------------
				// タイプ＝年月日 (yyyyMMdd)
				//------------------------------------------------
				if (!isDate($value)) {
					$result .= $nm . " は日付書式(yyyyMMdd)で入力してください。<br />";
				}
			}
			if ($type == "TIME4") {
				//------------------------------------------------
				// タイプ＝時分 (HHmm)
				//------------------------------------------------
				if (!isHM($value)) {
					$result .= $nm . " は時間書式(HHmm)で入力してください。<br />";
				}
			}
			if ($type == "TIME6") {
				//------------------------------------------------
				// タイプ＝時分秒 (HHmmss)
				//------------------------------------------------
				if (!isHMS($value)) {
					$result .= $nm . " は時間書式(HHmmss)で入力してください。<br />";
				}
			}
			if ($type == "TIME14") {
				//------------------------------------------------
				// タイプ＝日時 (yyyyMMddHHmmss)
				//------------------------------------------------
				if (!isDate14($value)) {
					$result .= $nm . " は日付書式(yyyyMMddHHmmss)で入力してください。<br />";
				}
			}
			if ($type == "TIME5") {
				//------------------------------------------------
				// タイプ＝時刻 (HH:mm)
				//------------------------------------------------
				if (!isTime($value)) {
					$result .= $nm . " は時間書式(HH:mm)で入力してください。<br />";
				}
			}
			if ($type == "TIME7") {
				//------------------------------------------------
				// タイプ＝時刻 (HH:mm:ss)
				//------------------------------------------------
				if (!isTime6($value)) {
					$result .= $nm . " は時間書式(HH:mm:ss)で入力してください。<br />";
				}
			}
			if ($type == "NONE") {
				//------------------------------------------------
				// タイプ＝タイプ指定なし（型チェックなし）
				//------------------------------------------------
			}
		}

		// e-mail確認
		//if ("" != TO_CSV_COL && $no == CHK_TO_CSV_COL) {
		//	$add_main = $lst_input[TO_CSV_COL];
		//	$add_chk  = $lst_input[CHK_TO_CSV_COL];
		//	if($add_main != "" || $add_chk != "") {
		//		if($add_main != $add_chk) {
		//			$result .= $lst_item[TO_CSV_COL - 1][1] . "と" . $lst_item[CHK_TO_CSV_COL - 1][1] . "の値が一致しません。<br />";
		//		}
		//	}
		//}
		if ("" != $chk_n && "end" != $chk_n) {
			$add_main = $lst_input[$no];
			$add_chk  = $lst_input[$chk_n];
			if($add_main != "" || $add_chk != "") {
				if($add_main != $add_chk) {
					$result .= $lst_item[$no - 1][1] . "と" . $lst_item[$chk_n - 1][1] . "の値が一致しません。<br />";
				}
			}
		}
	}
	return $result;
}


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
// ドロップダウン文字列取得関数
// 引数：$str       = 作成文字列
// 　　　$tagName   = 作成する[SELECTタグ]の名前
// 　　　$opt       = 作成した[SELECTタグ]のオプション
// 　　　$makeBlank = 作成した[SELECTタグ]の初期値 true:初期値空、false:初期値空なし
//------------------------------------------------
function getHtmlSelect($str, $tagName, $selectKey = "", $opt = "", $makeBlank = false)
{
	$str = str_replace("\n", "", $str);
	$str = str_replace("\r", "", $str);
	$lst = explode(CUT_COL, $str);

	// 取得したデータを展開
	$str = getSelectBox($tagName, $selectKey, $opt, $lst, $makeBlank, true);

	return $str;
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
// 関数名：文字列チェック　小数点チェック
//------------------------------------------------
function isDecimal($str)
{

	$pattern = "^([1-9]\d*|0)(\.\d+)?$";
	if (regularExpression($pattern, $str)) { return true; }
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
	$pattern = "(^[0-9]{1,4}-[0-9]{1,6}-[0-9]{1,8})|(^[0-9]{9})|(^[0-9]{10})$";
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

//------------------------------------------------
// IPアドレス取得
//------------------------------------------------
function getIP()
{
	$ip = array();

	if (preg_match('/^\d+(?:\.\d+){3}$/D', getServerInfo('HTTP_SP_HOST'))) {
		$ip[] = $_SERVER['HTTP_SP_HOST'];
	}
	if (preg_match('/.*\s(\d+(?:\.\d+){3})/', getServerInfo('HTTP_VIA'), $match)) {
		$ip[] = $match[1];
	}
	if (preg_match('/^\d+(?:\.\d+){3}/', getServerInfo('HTTP_CLIENT_IP'), $match)) {
		$ip[] = $match[0];
	}
	if (preg_match('/^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})/i', getServerInfo('HTTP_CLIENT_IP'), $match)) {
		$ip[] = implode('.', array(hexdec($match[1]), hexdec($match[2]), hexdec($match[3]), hexdec($match[4])));
	}
	if (preg_match('/.*\s(\d+(?:\.\d+){3})/', getServerInfo('HTTP_FORWARDED'), $match)) {
		$ip[] = $match[1];
	}
	if (preg_match('/^\d+(?:\.\d+){3}/', getServerInfo('HTTP_X_FORWARDED_FOR'), $match)) {
		$ip[] = $match[0];
	}
	if (preg_match('/^\d+(?:\.\d+){3}$/D', getServerInfo('HTTP_FROM'))) {
		$ip[] = $_SERVER['HTTP_FROM'];
	}

	$addr = '';
	foreach ($ip as $value) {
		if (!preg_match('/^(?:10|172\.16|192\.168|127\.0|0\.|169\.254)\./', $value) and $addr=$value) break;
	}

	return($addr ? $addr : $_SERVER['REMOTE_ADDR']);
}
function getServerInfo($key) {
	return(isset($_SERVER[$key]) ? $_SERVER[$key] : '');
}
