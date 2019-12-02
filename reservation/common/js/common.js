//------------------------------------------------------------------------------
// 共通関数
// バージョン：1.0.0
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2011. PLASOL Inc. All Right Reserved.
//------------------------------------------------------------------------------

//------------------------------------------------
// カレンダー選択機能
//------------------------------------------------
function initDatepicker()
{
	var append_head;
	append_head         = document.createElement("script");
	append_head.type    = "text/javascript";
	append_head.src     = "../../common/js/jquery-ui.min.js";
	append_head.charset = "utf-8";
	$(append_head).appendTo("body");

	// カレンダー
	jQuery.datepicker.regional['ja'] = {
		closeText: '閉じる',
		prevText: '前へ',
		nextText: '次へ',
		currentText: '今日',
		monthNames: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
		monthNamesShort: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
		dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
		dayNamesShort: ['日','月','火','水','木','金','土'],
		dayNamesMin: ['日','月','火','水','木','金','土'],
		weekHeader: '週',
		dateFormat: 'yy/mm/dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: '年'
	};
	jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ja']);
}

//------------------------------------------------
// 検証関数 初期処理
//------------------------------------------------
function addValidateCheck()
{
	// 電話番号チェック
	jQuery.validator.addMethod("tel", validateCheckPhone, " 番号を正しく入力してください。");
	// 郵便番号チェック
	jQuery.validator.addMethod("postcode", validateCheckPostcode, " 郵便番号を正しく入力してください。");
	// 全角ひらがなのみ
	jQuery.validator.addMethod("hiragana", validateCheckHiragana, " 全角ひらがなで入力してください");
	// 半角アルファベット（大文字･小文字）のみ
	jQuery.validator.addMethod("alphabet", validateCheckAlphabet, " 半角英字で入力してください。");
}
//------------------------------------------------
// 検証関数 日付
//------------------------------------------------
function validateCheckDate(value, element, params)
{
	var res = validateIsDate(value);
	return this.optional(element) || res;
}
//------------------------------------------------
// 検証関数 日付（実装部）
// @param val 日付文字列（/区切り）
//------------------------------------------------
function validateIsDate(val)
{
	var condition = /^\d{4}\/\d{1,2}\/\d{1,2}$/; // 書式指定
	if(!condition.test(val)){ return false; }

	var dates = val.split('/');

	var y = dates[0];
	var m = dates[1] * 1;
	var d = dates[2] * 1;

	// Date型に変換（変換できれば書式はＯＫ）
	var cd = new Date(y, m - 1, d); // 月は0～11で指定する

	// 年月日チェック
	// ※Dateで変換した場合、15月などにすると１年繰り上げて３月などとなる
	if(cd.getFullYear() != y) { return false; } // 入力元と同じかどうか
	if(cd.getMonth() != m - 1) { return false; } // 入力元と同じかどうか（月は0～11なので-1した値）
	if(cd.getDate() != d) { return false; } // 入力元と同じかどうか

	return true;
}
//------------------------------------------------
// 検証関数 全角ひらがなのみ
//------------------------------------------------
function validateCheckHiragana(value, element, params)
{
	return this.optional(element) || /^([ぁ-ん 　]+)$/.test(value);
}
//------------------------------------------------
// 検証関数 半角アルファベット（大文字･小文字）のみ
//------------------------------------------------
function validateCheckAlphabet(value, element, params)
{
	return this.optional(element) || /^([a-zA-z\s]+)$/.test(value);
}
//------------------------------------------------
// 検証関数 電話番号
//------------------------------------------------
function validateCheckPhone(value, element, params)
{
	return this.optional(element) || /^\d{2,4}-\d{2,4}-\d{2,4}$/.test(value);
}
//------------------------------------------------
// 検証関数 郵便番号
//------------------------------------------------
function validateCheckPostcode(value, element, params)
{
	return this.optional(element) || /^[0-9]{3}-[0-9]{4}$/.test(value);
}
