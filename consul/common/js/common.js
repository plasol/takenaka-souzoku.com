//------------------------------------------------------------------------------
// 共通関数
// バージョン：1.0.5
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2011. PLASOL Inc. All Right Reserved.
//------------------------------------------------------------------------------

//------------------------------------------------
// 標準関数
//------------------------------------------------
function exParseInt(val)
{
	if (isBlank(val)) { val = 0; }
	if (!jQuery.isNumeric(val)) { val = 0; }
	return parseInt(val, 10);
}
// 足し算
function add(val1, val2)
{
	return exParseInt(val1) + exParseInt(val2);
}
// ブランク判定
function isBlank(val)
{
	return(!val || jQuery.trim(val) === "");
}
// 書式変換（通貨）
function formatCurrency(val)
{
	return String(val).toString().replace(
		/^(-?[0-9]+)(?=\.|$)/,
		function(s) {
			return s.replace(/([0-9]+?)(?=(?:[0-9]{3})+$)/g, '$1,');
		}
	);
}

//------------------------------------------------
// カレンダー選択機能
//------------------------------------------------
function initDatepicker()
{
	// カレンダー
	jQuery.datepicker.regional['ja'] = {
		onSelect: function(dateText) { jQuery(this).change(); },
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
	//全角文字
	jQuery.validator.addMethod("ex_zen", function(value, element) {
		return this.optional(element) || /^(?:[^ -~｡-ﾟ]*)*$/.test(value);
		}, "全角文字を入力してください"
	);

	//全角ひらがな･カタカナのみ
	jQuery.validator.addMethod("ex_kana", function(value, element) {
		return this.optional(element) || /^([ァ-ヶーぁ-ん]+)$/.test(value);
		}, "全角ひらがな･カタカナを入力してください"
	);

	//全角ひらがなのみ
	jQuery.validator.addMethod("ex_hiragana", function(value, element) {
		return this.optional(element) || /^([ぁ-ん]+)$/.test(value);
		}, "全角ひらがなを入力してください"
	);

	//全角カタカナのみ
	jQuery.validator.addMethod("ex_katakana", function(value, element) {
		return this.optional(element) || /^([ァ-ヶー]+)$/.test(value);
		}, "全角カタカナを入力してください"
	);

	//半角カタカナのみ
	jQuery.validator.addMethod("ex_hankana", function(value, element) {
		return this.optional(element) || /^([ｧ-ﾝﾞﾟ]+)$/.test(value);
		}, "半角カタカナを入力してください"
	);

	//半角アルファベット（大文字･小文字）のみ
	jQuery.validator.addMethod("ex_alphabet", function(value, element) {
		return this.optional(element) || /^([a-zA-z¥s]+)$/.test(value);
		}, "半角英字を入力してください"
	);

	//半角アルファベット（大文字･小文字）もしくは数字のみ
	jQuery.validator.addMethod("ex_alphanum", function(value, element) {
		return this.optional(element) || /^([a-zA-Z0-9]+)$/.test(value);
		}, "半角英数字を入力してください"
	);

	//郵便番号（例:012-3456）
	jQuery.validator.addMethod("ex_postnum", function(value, element) {
		return this.optional(element) || /^[0-9]{3}-[0-9]{4}$/.test(value);
		}, "郵便番号を入力してください（例:123-4567）"
	);

	//携帯番号（例:010-2345-6789）
	jQuery.validator.addMethod("ex_mobilenum", function(value, element) {
		return this.optional(element) || /^0¥d0-¥d{4}-¥d{4}$/.test(value);
		}, "携帯番号を入力してください（例:010-2345-6789）"
	);

	//電話番号（例:012-345-6789）
	jQuery.validator.addMethod("ex_telnum", function(value, element) {
		return this.optional(element) || /^[0-9-]*$/.test(value);
		}, "電話番号を入力してください（例:012-345-6789）"
	);

	//スペース不可
	jQuery.validator.addMethod("ex_nospace", function(value, element) {
		return !(/\s/.test(value))
		}, "スペースは入力できません。"
	);
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
