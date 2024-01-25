<?php 
error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
//
$mail_sys = "kazusa0171@gmail.com";	// 管理者のメールアドレス
$from_name = "handmadeshop moa🌼";	// メール送信者の表示
$from_mail = "moa2024012@ymail.ne.jp";	// メール送信者のメールアドレス（返信先）
$user_mail = "item2";	// 利用者にメールを送る場合のメールアドレス項目
//---
$title = "お問い合わせフォーム";
$subject = "お問い合わせ有難うございます\n";
$body = "お問い合わせ有難うございます。
以下の内容で承りました。
\n";
$subject_sys = "お問い合わせがありました\n";
$body_sys = "\n";
$footer = "\n------------

http://〜〜〜〜〜.com/
------------
";
//------------------------------------------------

?>
<?php
/*
 * PHPフォーム処理
 *
 * 複数添付ファイル対応
 * XHTML対応（XML宣言の処理）
 * 入力のエスケープ処理 20190411
 * キャプチャ機能追加
 *
 * 2011-2022 (c) Crytus
 */

ini_set("short_open_tag", "0");
ini_set("magic_quotes_gpc", "0");
ini_set("mbstring.encoding_translation", "0");

// HTMLやプログラムの漢字コード（SJISにする場合は、HTMLを含めコードの変更が必要です）
//define("SCRIPT_ENCODING", "SJIS");
define("SCRIPT_ENCODING", "UTF-8");
// メール自体の漢字コードの指定です（英語・日本語の場合はJISを、それ以外の言語はUTF8にしてください）
define("MAIL_ENCODING", "JIS");
//define("MAIL_ENCODING", "UTF8");

// セッションを使用します（セッションが有効で無いとキャプチャは動作しません）
session_start();

// キャプチャ用画像処理
if (isset($_REQUEST["act"]) && ($_REQUEST["act"] == "captcha")) {
	captcha();
}

// キャプチャ画像出力URL
$ary = explode("/", $_SERVER["REQUEST_URI"]);
$ary[count($ary)-1] = basename(__FILE__);
$captcha = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . implode("/", $ary) . "?act=captcha";

if (!$form_html) {
	$form_html = "form.html";		// 入力フォームのファイル名
}
if (!$confirm_html) {
	$confirm_html = "confirm.html";		// 確認画面のファイル名
}
if (!$finish_html) {
	$finish_html = "finish.html";		// 終了画面のファイル名
}
// ユーザー向け
if (!$subject) {
	$subject = "お問い合わせ有難うございます\n";
}
if (!$body) {
	$body = "お問い合わせ有難うございます。
以下の内容で承りました。
\n";
}
// 管理者向け
if (!$subject_sys) {
	$subject_sys = "お問い合わせがありました\n";
}
if (!$body_sys) {
	$body_sys = "\n";
}
// メール本文後ろ（改行に注意）
if (!$footer) {
	$footer = "\n------------
	handmadeshop moa🌼
http://〜〜〜〜〜.com/
------------
";
}
//	都道府県
$pref_list = array(	
	"1" => "北海道",
	"2" => "青森県",
	"3" => "岩手県",
	"4" => "宮城県",
	"5" => "秋田県",
	"6" => "山形県",
	"7" => "福島県",
	"8" => "茨城県",
	"9" => "栃木県",
	"10" => "群馬県",
	"11" => "埼玉県",
	"12" => "千葉県",
	"13" => "東京都",
	"14" => "神奈川県",
	"15" => "新潟県",
	"16" => "富山県",
	"17" => "石川県",
	"18" => "福井県",
	"19" => "山梨県",
	"20" => "長野県",
	"21" => "岐阜県",
	"22" => "静岡県",
	"23" => "愛知県",
	"24" => "三重県",
	"25" => "滋賀県",
	"26" => "京都府",
	"27" => "大阪府",
	"28" => "兵庫県",
	"29" => "奈良県",
	"30" => "和歌山県",
	"31" => "鳥取県",
	"32" => "島根県",
	"33" => "岡山県",
	"34" => "広島県",
	"35" => "山口県",
	"36" => "徳島県",
	"37" => "香川県",
	"38" => "愛媛県",
	"39" => "高知県",
	"40" => "福岡県",
	"41" => "佐賀県",
	"42" => "長崎県",
	"43" => "熊本県",
	"44" => "大分県",
	"45" => "宮崎県",
	"46" => "鹿児島県",
	"47" => "沖縄県",
	"48" => "海外",
	"99" => "非公開",
);
//------------------------------------------------
//
$form_input = array(
	"item1" => array("title" => "お名前", "name" => "item1", "func" => "2", "require" => "1", "check" => "1",),
	"item2" => array("title" => "メールアドレス", "name" => "item2", "func" => "2", "require" => "1", "check" => "3",),
	"item3" => array("title" => "お問い合わせ内容", "name" => "item3", "func" => "7", "require" => "1", "check" => "1",),

);
// 入力値の取得
$msg = array();
$mail = array();
$mail_field = array();
foreach ($form_input as $val) {
	$value = 0;
	if ($val["func"] == 6) {
		if (is_array($_REQUEST[$val["name"]])) {
			foreach ($_REQUEST[$val["name"]] as $k => $v) {
				$form[$val["name"]][$k] = htmlspecialchars($v);
				if ($v) {
					$value = 1;
				}
			}
		} else {
			$form[$val["name"]] = htmlspecialchars($_REQUEST[$val["name"]]);
			if ($form[$val["name"]]) {
				foreach ($form[$val["name"]] as $v) {
					if ($v != "") {
						$value = 1;
					}
				}
			}
		}
	} else if ($val["func"] == 10) {
		$form[$val["name"] . "_pref"] = htmlspecialchars($_REQUEST[$val["name"] . "_pref"]);
		$form[$val["name"] . "_address"] = htmlspecialchars($_REQUEST[$val["name"] . "_address"]);
		if ($form[$val["name"] . "_pref"] && $form[$val["name"] . "_address"]) {
			$value = 1;
		}
	} else if ($val["func"] == 11) {
		$form[$val["name"] . "_year"] = htmlspecialchars($_REQUEST[$val["name"] . "_year"]);
		$form[$val["name"] . "_month"] = htmlspecialchars($_REQUEST[$val["name"] . "_month"]);
		$form[$val["name"] . "_day"] = htmlspecialchars($_REQUEST[$val["name"] . "_day"]);
		if ($form[$val["name"] . "_year"] && $form[$val["name"] . "_month"] && $form[$val["name"] . "_day"]) {
			$value = 1;
		}
	} else if ($val["func"] == 12) {
		$form[$val["name"] . "_month"] = htmlspecialchars($_REQUEST[$val["name"] . "_month"]);
		$form[$val["name"] . "_day"] = htmlspecialchars($_REQUEST[$val["name"] . "_day"]);
		if ($form[$val["name"] . "_month"] && $form[$val["name"] . "_day"]) {
			$value = 1;
		}
	} else if ($val["func"] == 13) {	// File
		if ($_FILES[$val["name"]]["name"]) {
			// 添付ファイルへの処理をします。
			$handle = fopen($_FILES[$val["name"]]["tmp_name"], 'r');
			$attachFile = fread($handle, filesize($_FILES[$val["name"]]["tmp_name"]));
			fclose($handle);
			$attachEncode = base64_encode($attachFile);
			$form[$val["name"] . "_value"] = $attachEncode;
			$form[$val["name"] . "_file"] = $_FILES[$val["name"]]["name"];
			$form[$val["name"] . "_type"] = $_FILES[$val["name"]]["type"];
			$value = 1;
		} else if ($_REQUEST[$val["name"] . "_value"]) {
			$form[$val["name"] . "_value"] = $_REQUEST[$val["name"] . "_value"];
			$form[$val["name"] . "_file"] = $_REQUEST[$val["name"] . "_file"];
			$form[$val["name"] . "_type"] = $_REQUEST[$val["name"] . "_type"];
			$value = 1;
		}
	} else {
		if (is_array($_REQUEST[$val["name"]])) {
			foreach ($_REQUEST[$val["name"]] as $k => $v) {
				$form[$val["name"]][$k] = htmlspecialchars($v);
				if ($v) {
					$value = 1;
				}
			}
		} else {
			$form[$val["name"]] = htmlspecialchars($_REQUEST[$val["name"]]);
			if ($form[$val["name"]]) {
				$value = 1;
			}
		}
	}
	// 入力のチェック
	if ($_REQUEST["mode"] == "form") {
		if ($val["require"] && ($value == 0)) {
			$msg[$val["name"]] = $val["title"] . "が入力されていません。";
		}
		if ($val["check"] && $value) {
			if ($val["check"] == 2) {	// 電話
				if (!preg_match("/^[0-9\-]+$/", $form[$val["name"]])) {
					$msg[$val["name"]] = $val["title"] . "が正しくありません";
				}
			}
			if (($val["check"] == 3)||($val["check"] == 4)) {	// メール
				if (!preg_match("/^[0-9a-zA-Z_\.\-]+@[0-9a-zA-Z_\.\-]+$/", $form[$val["name"]])) {
					$msg[$val["name"]] = $val["title"] . "が正しくありません";
				} else {
					$mail_value[] = $form[$val["name"]];
					$mail_field[] = $val["name"];
					$mail_title[] = $val["title"];
				}
			}
			if ($val["check"] == 5) {	// URL
				if (!preg_match("/^(http|https):\/\/[0-9a-zA-Z_\.\-\/]+$/", $form[$val["name"]])) {
					$msg[$val["name"]] = $val["title"] . "が正しくありません";
				}
			}
			if ($val["check"] == 6) {	// キャプチャ
				if ($form[$val["name"]] != $_SESSION["captcha"]) {
					$msg[$val["name"]] = $val["title"] . "が正しくありません";
					$form[$val["name"]] = "";
				}
			}
		}
	}
}
// メール一致
if (x_count($mail_value) == 2) {
	if ($mail_value[0] != $mail_value[1]) {
		$msg[$mail_field[0]] = $mail_title[0] . "が一致していません";
		$msg[$mail_field[1]] = $mail_title[0] . "が一致していません";
	}
}
if (!$_REQUEST["mode"]) {
	$mode = "form";
} else if ($_REQUEST["mode"] == "reinput") {
	$mode = "form";
} else if ($_REQUEST["mode"] != "confirm") {
	if ($msg) {
		$mode = "form";
	} else {
		$mode = "confirm";
	}
} else {
	// メールの送信
	// 本文へ入力値の設定
	foreach ($form_input as $key => $val) {
		if ($val["func"] == 13) {	// File
			$mail_body .= "■" . $val["title"] . "：" . $form[$val["name"] . "_file"] . "\n";
		} else if ($val["func"] == 10) {
			$mail_body .= "■" . $val["title"] . "：" . $pref_list[$form[$val["name"] . "_pref"]] . $form[$val["name"] . "_address"] . "\n";
		} else if ($val["func"] == 11) {
			$mail_body .= "■" . $val["title"] . "：" . $form[$val["name"] . "_year"] . "年" . 
				$form[$val["name"] . "_month"] . "月" . $form[$val["name"] . "_day"] . "日\n";
		} else if ($val["func"] == 12) {
			$mail_body .= "■" . $val["title"] . "：" . $form[$val["name"] . "_month"] . "月" . $form[$val["name"] . "_day"] . "日\n";
		} else if ($val["func"] == 3) {	// 単一選択（ラジオボタン）
			$mail_body .= "■" . $val["title"] . "：" . $form_input[$val["name"]]["list"][$form[$val["name"]]] . "\n";
		} else if ($val["func"] == 4) {	// 複数選択（チェックボックス）
			if ($form[$val["name"]]) {
				$ary = array();
				foreach ($form[$val["name"]] as $val2) {
					$ary[] = $form_input[$val["name"]]["list"][$val2];
				}
				$mail_body .= "■" . $val["title"] . "：" . implode("、", $ary) . "\n";
			}
		} else if ($val["func"] == 5) {	// 選択（プルダウン）
			$mail_body .= "■" . $val["title"] . "：" . $form_input[$val["name"]]["list"][$form[$val["name"]]] . "\n";
		} else if ($val["func"] == 6) {	// 複数個1行テキスト入力
			$mail_body .= "■" . $val["title"] . "：\n";
			foreach ($form_input[$val["name"]]["list"] as $key => $val2) {
				$mail_body .= "　" . $val2 . "：" . $form[$val["name"]][$key] . "\n";
			}
		} else if ($val["func"] == 14) {	// キャプチャ
			// キャプチャが存在する場合、そのチェックが通らないとメールの送信はしない
			if (($form[$val["name"]] == "")||($_SESSION["captcha"] == "")) {
				exit;
			}
			if ($_SESSION["captcha"] != $form[$val["name"]]) {
				exit;
			}
		} else {
			$mail_body .= "■" . $val["title"] . "：" . $form[$val["name"]] . "\n";
		}
	}
	unset($_SESSION["captcha"]);
	$mail_body .= $footer;
	$attach = array();
	foreach ($form_input as $val) {
		if ($val["func"] == 13) {	// File
			if ($_REQUEST[$val["name"] . "_value"]) {	// 添付ファイルあり
				$attach[] = array(
					"filebody" => $_REQUEST[$val["name"] . "_value"],
					"filename" => $_REQUEST[$val["name"] . "_file"],
					"filetype" => $_REQUEST[$val["name"] . "_type"]);
			}
		}
	}
	if ($mail_sys) {
		// 管理者向け
		sendmail($from_mail, $mail_sys, $subject_sys, $body_sys . $mail_body, $attach, $from_name);
	}
	//
	if (isset($user_mail) && $_REQUEST[$user_mail]) {
		// 利用者向け
		sendmail($from_mail, $_REQUEST[$user_mail], $subject, $body . $mail_body, $attach, $from_name);
	}
	//
	$mode = "finish";
}

if ($mode == "confirm") {
	$contents = file_get_contents($confirm_html);
} else if ($mode == "finish") {
	$contents = file_get_contents($finish_html);
} else {
	$contents = file_get_contents($form_html);
}

$head = "";
if (mb_ereg("^<\?([^\?])+\?>", $contents, $m)) {
	// XML宣言があったら除外
	$head = $m[0];
	$contents = substr($contents, strlen($head));
}
// HTML出力
echo $head;
echo eval("?>" . $contents);

/*
 * メール送信処理、本文を変数から
 *
 * 差し込み処理、長いサブジェクトに対応
 */
function sendmail($mail_from, $mail_to, $mail_subject, $body, $attach, $from_name=null)
{
	$mail_from = trim($mail_from);
	$mail_to = trim($mail_to);
	$mail_subject = trim($mail_subject);
	$body = html_entity_decode(trim($body));
	$reply = trim($reply);
	$from_name = trim($from_name);
	// 送信元情報の作成
	if ($from_name) {
		$header = "From: ";
		if (MAIL_ENCODING == "UTF8") {
			$header .= '=?utf-8?B?';
		} else {
			$header .= '=?iso-2022-jp?B?';
		}
		$header .= base64_encode(mb_convert_encoding($from_name, MAIL_ENCODING, SCRIPT_ENCODING)) .
			 '?= <' . $mail_from . ">";
	} else {
		$header = "From: " . $mail_from;
	}
	if ($attach) {	// ファイル添付
		$uniq_id = uniqid('boundary');
		$header .= "\r\nMIME-Version: 1.0";
		$header .= "\r\nContent-Type: multipart/mixed; boundary=\"" . $uniq_id . "\"\r\n";
		// 本文の加工
		$mailMessage = $body;
		$body  = "--" . $uniq_id . "\r\n";
		if (MAIL_ENCODING == "UTF8") {
			$body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
		} else {
			$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\r\n";
		}
		$body .= "\r\n";
		$body .= $mailMessage . "\r\n";

		foreach ($attach as $val) {
			$filename = $val["filename"];
			$filebody = $val["filebody"];
			$filetype = $val["filetype"];

			if (MAIL_ENCODING == "UTF8") {
				$fname = '=?utf-8?B?' . base64_encode(mb_convert_encoding($filename, MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			} else {
				$fname = '=?iso-2022-jp?B?' . base64_encode(mb_convert_encoding($filename, MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			}

			$body .= "--" . $uniq_id . "\r\n";

			$body .= "Content-Type: $filetype; name=\"$filename\"\r\n";
			$body .= "Content-Transfer-Encoding: base64\r\n";
			$body .= "Content-Disposition: attachment; filename=\"$fname\"\r\n";
			$body .= "\r\n";
			$body .= chunk_split($filebody) . "\r\n";
		}
		$body .= "--" . $uniq_id . "--\r\n";
	} else {
		// 漢字コードの指定
		if (MAIL_ENCODING == "UTF8") {
			$header .= "\r\nContent-Type: text/plain;\r\n\tformat=flowed;\r\n\tcharset=\"utf-8\";\r\n\treply-type=original";
		} else {
			$header .= "\r\nContent-Type: text/plain;\r\n\tcharset=\"iso-2022-jp\"\r\nContent-Transfer-Encoding: 7bit";
		}
	}
	// 返信先指定
	if ($reply) {
		$header .= "\r\nReply-to: <" . $reply . ">";
	}
	// 件名の変換
	$subject_str = '';
	while ($mail_subject) {
		if ($subject_str) {
			$subject_str .= "\r\n\t";
		}
		if (mb_strlen($mail_subject, SCRIPT_ENCODING) < 20) {
			if (MAIL_ENCODING == "UTF8") {
				$subject_str .= '=?utf-8?B?';
			} else {
				$subject_str .= '=?iso-2022-jp?B?';
			}
			$subject_str .= base64_encode(mb_convert_encoding($mail_subject, MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			$mail_subject = '';
		} else {
			if (MAIL_ENCODING == "UTF8") {
				$subject_str .= '=?utf-8?B?';
			} else {
				$subject_str .= '=?iso-2022-jp?B?';
			}
			$subject_str .= base64_encode(mb_convert_encoding(mb_substr($mail_subject, 0, 20, SCRIPT_ENCODING), MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			$mail_subject = mb_substr($mail_subject, 20, mb_strlen($mail_subject, SCRIPT_ENCODING) - 20, SCRIPT_ENCODING);
		}
	}
	$body = str_replace("\r", "", $body);
	$header = str_replace("\r", "", $header);
	// 送信処理
	return @mail($mail_to, $subject_str, mb_convert_encoding($body, MAIL_ENCODING, SCRIPT_ENCODING), $header);
}
//
if (!function_exists("safeStripSlashes")) {
function safeStripSlashes($var) {
  if (is_array($var)) {
    return array_map('safeStripSlashes', $var);
  } else {
    return $var;
  }
}
}
//
function value($key)
{
	if (is_array($_REQUEST[$key])) {
		return join(",", $_REQUEST[$key]);
	}
	return $_REQUEST[$key];
}
function x_count($item)
{
	if (is_array($item)) {
		return count($item);
	}
	return 0;
}
// 1文字画像化
function imgch($ch)
{
	$im = imagecreate(20, 20);
	// 文字黒、背景白
	$bg = imagecolorallocate($im, 255, 255, 255);
	$textcolor = imagecolorallocate($im, 0, 0, 0);

	// 文字を描画
	imagestring($im, 10, 3, 3, $ch, $textcolor);

	// 傾き
	$r = mt_rand(-45, 45);
	$im2 = imagerotate($im, $r, 0);

	imagedestroy($im);
	return $im2;
}
// CAPTCHA画像作成
function captcha()
{
	session_start();

	// 出力する4文字を決定
	$ch1 = chr(mt_rand(65, 65 + 25));
	$ch2 = chr(mt_rand(65, 65 + 25));
	$ch3 = chr(mt_rand(65, 65 + 25));
	$ch4 = chr(mt_rand(65, 65 + 25));

	// 確認用にセッションへ保存
	$_SESSION["captcha"] = $ch1 . $ch2 . $ch3 . $ch4;

	$imgCh[] = $ch1;
	$imgCh[] = $ch2;
	$imgCh[] = $ch3;
	$imgCh[] = $ch4;

	$img = imagecreatetruecolor(104, 30);
	imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));

	// 4文字合成
	$px = 3;	// 合成位置
	foreach ($imgCh as $ch) {
 		$img2 = imgch($ch);

 		// 画像のサイズを取得
		$sx = imagesx($img2);
		$sy = imagesy($img2);
		if ($sx < $sy) {
			$sx = $sy;
		} else {
			$sy = $sx;
		}
		imagecopy($img, $img2, $px, 2, 0, 0, $sx - 1, $sy); // 合成する
		$px += $sx;

		imagedestroy($img2);
	}
	$sx = imagesx($img);
	$sy = imagesy($img);

	// 横線を引く
	$color = imagecolorallocate($img, 0, 0, 0);
	imageline($img, 0, 10, $sx - 1, 10, $color);
	imageline($img, 0, 18, $sx - 1, 18, $color);

	// 2倍に拡大
	$img2 = imagecreatetruecolor($sx * 2, $sy * 2);
	imagecopyresized($img2, $img, 0, 0, 0, 0, $sx * 2, $sy * 2, $sx, $sy);

	// 画像出力
	header("Content-type: image/png");
	imagepng($img2);
	//
	imagedestroy($img);
	imagedestroy($img2);
	exit;
}