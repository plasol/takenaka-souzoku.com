<?php
//------------------------------------------------------------------------------
// システム名：問い合わせフォーム
// 機能名　　：共通関数
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2010. PLASOL Inc. All Right Reserved.
//------------------------------------------------------------------------------


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
// 　　　　post_value = POSTで取得した項目値
// 　　　　lst_mail   = （送信時のみ）追加アドレスリスト
//--------------------------------------------------
function setFormRelace($kbn_field, &$str_html, $set_colmns, $set_value, $lst_mail = '')
{
	$sep_mail = STRING_REPLACE_SEPARATOR_MAIL; // 区切り文字（問い合わせ）

	$i = 0;
	$str_body = "";
	$lst = $set_colmns;
	$con = '';
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

		$value = $set_value[$no]; // POST値
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
			$validate = str_replace("{$sep_mail}ID{$sep_mail}", $nm_item, $validate);
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
					$tmp = "%s<textarea %s %s %s %s %s %s>%s</textarea>%s";
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
					$idx=1;
					while(is_array($lst_data) && list(, $val_1) = each($lst_data)) {
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
						$str_html = str_replace("{$sep_mail}TAG" . $no . "_" . $idx++ . "{$sep_mail}",  $control, $str_html);
					}
					break;
			}
		}

		//------------------------------------------------
		// 確認
		//------------------------------------------------
		if($kbn_field == 1) {
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
				$vals = $back . getFormatString($value, STR_ENC_SYS_TO_HTML) . $forth;
			}
			$str_html = str_replace("{$sep_mail}NAME" . $no . "{$sep_mail}", $nm, $str_html);
			$str_html = str_replace("{$sep_mail}TAG" . $no . "{$sep_mail}",  $vals, $str_html);
		}
		$i++;

		//------------------------------------------------
		// メール送信
		//------------------------------------------------
		if($kbn_field == 2) {
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
				$vals = $value;
			}

			// メール項目追加
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
		$con = $con_n;
	}

	if($kbn_field == 2) {
		$from = FROM;

		if (ST_SEND_PATTERN == 1) {
			// ▼通常
			// ※管理者アドレスが未設定の場合、メールは「全て送信されません」
			// ※追加アドレスが設定されている（ex.datが存在する）場合、３．が実行されます。

			// １．管理者 → 一般ユーザー　　　　　　　　※一般ユーザーが未設定の場合、メールは「送信されません」
			// ２．一般ユーザー → 管理者　　　　　　　　※一般ユーザーが未設定の場合、管理者として「送信する」
			// ３．管理者 → 追加アドレス（複数）　　　　※追加アドレス（複数）が未設定の場合、メールは「送信されません」

			if(FROM == "") { return false; }

			$guest_body = convert_mail_data(GUEST_HEADER . $str_body . GUEST_FOOTER);
			$guest_subject = convert_mail_data(GUEST_SUBJECT);
			$multi_body = convert_mail_data(MULTI_HEADER . $str_body . MULTI_FOOTER);
			$multi_subject = convert_mail_data(MULTI_SUBJECT);
			$owner_body = convert_mail_data(OWNER_HEADER . $str_body . OWNER_FOOTER);
			$owner_subject = convert_mail_data(OWNER_SUBJECT);

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

			$guest_body = convert_mail_data(GUEST_HEADER . $str_body . GUEST_FOOTER);
			$guest_subject = convert_mail_data(GUEST_SUBJECT);
			$multi_body = convert_mail_data(MULTI_HEADER . $str_body . MULTI_FOOTER);
			$multi_subject = convert_mail_data(MULTI_SUBJECT);
			$owner_body = convert_mail_data(OWNER_HEADER . $str_body . OWNER_FOOTER);
			$owner_subject = convert_mail_data(OWNER_SUBJECT);

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
	}
}

/**
 * メール送信
 * @param string $to 送信先
 * @param string $subject 件名
 * @param string $body 本文
 * @param string $from 送信元
 * @param bool $jis_flag JISで変換するかどうか
 * @return array ユーザー情報
 */
function sendMail($to, $subject, $body, $from, $jis_flag = false)
{
	// メールヘッダー
	$headers  = "From: {$from}" . "\n";
	$headers .= "Reply-To: {$from}" . "\n";
	$headers .= "MIME-Version: 1.0" . "\n";
	$headers .= "Date: " . date( "r") . "\n";

	$encode = 'UTF-8';
	if ($jis_flag) {
		$encode = 'JIS';
	}

	$headers .= "Content-type: text/plain; charset={$encode}" . "\n";
	$headers .= "Content-Transfer-Encoding: base64" . "\n";

	$body    = mb_convert_encoding($body, $encode, "auto");    // 本文
	$body    = base64_encode($body);

	$encode = 'UTF-8';

	// mb_internal_encoding保管
	$encode_main = mb_internal_encoding();

	// 文字コードを一致させる（mb_internal_encoding = 件名の文字コード）
	mb_internal_encoding($encode);
	$subject = mb_convert_encoding($subject, $encode, mb_detect_encoding($subject));

	// 件名をISO-2022-JPに変換
	$subject = mb_encode_mimeheader($subject);

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
		$value = $lst_input[$no];

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
// ドロップダウン文字列取得関数
// 引数：$str       = 作成文字列
// 　　　$tagName   = 作成する[SELECTタグ]の名前
// 　　　$opt       = 作成した[SELECTタグ]のオプション
// 　　　$makeBlank = 作成した[SELECTタグ]の初期値 true:初期値空、false:初期値空なし
//------------------------------------------------
function getHtmlSelect($str, $tagName, $selectKey = "", $opt = "", $makeBlank = false)
{
	// 要素配列
	$lst = array();
	if ($makeBlank) {
		$lst[""] = "";
	}

	$lst = array();
	$str = str_replace("\n", "", $str);
	$str = str_replace("\r", "", $str);
	$lst = explode(CUT_COL, $str);

	// 取得したデータを展開
	$str = getSelectBox($tagName, $selectKey, $opt, $lst, $makeBlank, true);

	return $str;
}
