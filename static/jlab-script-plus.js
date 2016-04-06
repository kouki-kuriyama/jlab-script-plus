<!--

//各変数の初期化
var BinaryData = Array();
var DeleteKey;
var EnableFileAPI;
var EnhanceUploader = false;
var ImageBoxNum = 0;
var MultiUploadLimit = 10;
var OpenURLBox = false;
var PhotoReader;
var Processing = false;
var RawFileData = Array();
var RawFileDataCount = 0;
var ReadyImageNum = 0;
var xmlRequest = new XMLHttpRequest();

//バージョン情報を設定
var VersionNumber = "jlab-script-plus Ver0.07a";

//FileAPIが使用できるか確認する
function CheckEnableFileAPI(){

	if( window.File && window.FileReader && UseEnhanceUploader ){
		EnableFileAPI = true;
		PhotoReader = new FileReader();
	}else{
		EnableFileAPI = false;
		document.getElementById("UploaderVersion").innerHTML = "Basicモード";
		document.getElementById("UploadMedia").multiple = false;
	}
	
	return;

}

//ファイルオーバー中
function onFileOver(e){
	if( EnableFileAPI ){
		e.preventDefault();
		document.getElementById("DragDropCurtainT").innerHTML = "ドロップして画像を取り込みます";
		document.getElementById("DragDropCurtain").style.top = "0px";
	}
}

//エンハンスアップロード（ドラッグアンドドロップ）
function onFileDrop(e){
	if( EnableFileAPI ){
		e.stopPropagation();
		e.preventDefault();
		RawFileData = e.dataTransfer.files;
		RawFileDataCount = e.dataTransfer.files.length;
		FileLoad(0);
	}
}

//エンハンスアップロード（ファイル選択ダイアログ）
function onFileSelected(){
	if( EnableFileAPI ){
		RawFileData = document.getElementById("UploadMedia").files;
		RawFileDataCount = document.getElementById("UploadMedia").files.length;
		FileLoad(0);
	}else{
		document.getElementById("Preview").innerHTML = "<span style=\"color:#ccc\">プレビューは表示されません</span>";
	}
}

//指定された画像を読み込んでBinaryDataに代入する
function FileLoad(Analyzing){
	
	//アップロード上限を超えている場合は停止
	if( ReadyImageNum >= MultiUploadLimit ){
		document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
		alert("同時に画像は" + MultiUploadLimit + "枚までです");
		return false;
	}
	
	//アップロードファイルの存在確認
	if( !RawFileData[Analyzing] ){
		document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
		alert("ファイルを確認してください");
		return false;
	}
	
	//アップロードファイルの簡易確認
	if( !RawFileData[Analyzing].type.match(/image\/jpeg/) && !RawFileData[Analyzing].type.match(/image\/png/) && !RawFileData[Analyzing].type.match(/image\/gif/)){
		document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
		alert("アップロードできる形式ではありません");
		return false;
	}
	
	//アップロードファイルを読み込む
	PhotoReader.onloadend = function(){
	
		//Base64形式を取り出す
		BinaryData64 = PhotoReader.result;
		
		//同じ画像が取り込まれていないか確認する
		if( 2 <= ImageBoxNum ){
			for( var SizeCheck=1; SizeCheck <= ImageBoxNum; SizeCheck++ ){
				if( BinaryData[SizeCheck] == undefined ){
					continue;
				}
				
				if( BinaryData64 == BinaryData[SizeCheck] ){
					document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
					setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
					alert("同じ画像を同時にアップロードすることはできません");
					return false;
				}
			}
		}
		
		//配列番号1から画像データを取り込む
		ImageBoxNum++;
		
		//プレビューを表示する
		if( RawFileData[Analyzing].size < 3145728 ){
		
			//プレビュー用imgタグを作成する
			PreviewImageTag = document.createElement("img");
			PreviewImageTag.src = BinaryData64;
			
			//キャンセル用aタグを作成する
			ClickLinkTag = document.createElement("a");
			ClickLinkTag.href = "javascript:ImageCancel(" + ImageBoxNum + ")";
			ClickLinkTag.title = "この画像を取り消しますか？ 取り消す場合には画像をクリックします。";
			ClickLinkTag.id = "PreviewImage" + ImageBoxNum;
			ClickLinkTag.appendChild(PreviewImageTag);
			document.getElementById("Preview").appendChild(ClickLinkTag);
			
		}
		
		//BinaryDataに代入する
		BinaryData[ImageBoxNum] = BinaryData64;
		
		//エンハンスアップローダー有効
		EnhanceUploader = true;
		
		//アナライズ用変数・ボックス変数・アップロード待機枚数を追加する
		Analyzing++;
		ReadyImageNum++;
		
		//取り込む画像が複数ある場合
		if( Analyzing < RawFileDataCount ){
			FileLoad(Analyzing);
		}
		
		return;
		
	};
	
	//URLスキーム形式で取得する
	PhotoReader.readAsDataURL(RawFileData[Analyzing]);
	
	//メッセージ表示
	document.getElementById("DragDropCurtainT").innerHTML = "取り込み完了";
	setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",1000);
	window.scroll(0,0);
	
	return;
}

//選択した画像をクリアする
function ImageCancel( SelectImageBoxNum ){

	//確認ダイアログ
	if( window.confirm("画像を取り消しますか？")){
		
		//配列から削除
		BinaryData[SelectImageBoxNum] = "Canceled";
		ReadyImageNum--;
		document.getElementById("PreviewImage" + SelectImageBoxNum).innerHTML = "";
		
	}
	
	return;
}

//読み込んだ画像をクリアする
function AllClear(Mode){

	if( Mode == "Complete" ){
		Processing = false;
		document.getElementById("UploaderCurtain").style.display = "none";
		document.getElementById("UploaderCurtainMessage").innerHTML = "";
		document.getElementById("UploaderCurtainTextBox").style.display = "none";
		document.getElementById("UploaderCurtainTextBox").value = "";
		document.getElementById("ResultButtonArea").style.display = "none";
	}
	
	if( EnhanceUploader ){
		document.getElementById("UploadMedia").style.display = "inline";
		document.getElementById("LoadedFileName").innerHTML = "";
		BinaryData = Array();
		EnhanceUploader = false;
		ImageBoxNum = 0;
		RawFileData = Array();
		ReadyImageNum = 0;
	}else{
		document.getElementById("FileTag").innerHTML = "<input type=\"file\" name=\"Image\" id=\"UploadMedia\" onchange=\"onFileSelected()\">";
	}
	document.getElementById("Preview").innerHTML = "";
	
	return;
	
}



//画像をアップロード
//エンハンスアップロードの場合はAjaxで送信
function ImageUploading(){

	//アップロードを開始
	if( !Processing ){
		Processing = true;
	}else{
		return false;
	}

	//エンハンスアップローダー
	if( EnhanceUploader ){
	
		//ファイルチェック
		if( BinaryData == "" ){
			alert("画像を選択してください");
			Processing = false;
			return false;
		}
		
		//アップロード中を表示する
		document.getElementById("UploaderCurtain").style.display = "block";
		
		//削除キーを取得する
		DeleteKey = document.getElementById("DeleteKeyBox").value;
		
		//初期化
		var Uploading = 0;
		
		//アップロード用関数
		GoUpload = function(){
			
			//次の配列の画像をアップロード
			//（1枚目の場合は[1]のアップロード）
			Uploading++;
			
			//POSTデータを作成する
			//（Canceledはユーザー側で取り消しを行い配列にデータが存在しない場合）
			if(( BinaryData[Uploading] == undefined )||( BinaryData[Uploading] == "" )){
				alert("エラーが発生しました\nページを更新してください");
				return;
			}else if( BinaryData[Uploading] == "Canceled" ){
				GoUpload();
				return;
			}
			
			//メッセージ表示
			document.getElementById("UploaderCurtainMessage").innerHTML = "アップロード中です…";
			
			//Ajax送信
			xmlRequest.open("POST","./upload.php",true);
			xmlRequest.onreadystatechange = ResultFlush;
			xmlRequest.setRequestHeader("content-type","application/x-www-form-urlencoded;charset=UTF-8");
			xmlRequest.send("Image=" + BinaryData[Uploading] + "&Uploading=" + Uploading + "&MaxBox=" + ImageBoxNum + "&DeleteKey=" + DeleteKey + "&Type=Enhance");
			
		}
		
		GoUpload();
		
		//アップロードが完了した順にAjaxでフラッシュしていく
		function ResultFlush(){
		
			if( xmlRequest.readyState == 4 ){
				
				//レスポンスを受け取る
				var UploadResponse = decodeURIComponent(xmlRequest.responseText);
				
				//アップロードエラー時の処理
				if( UploadResponse == "100" ){
					alert("この画像はアップロードできません : 容量制限");
					xmlRequest.abort();
					Processing = false;
					return false;
				}else if( UploadResponse == "200" ){
					alert("この画像はアップロードできません : 不明形式");
					xmlRequest.abort();
					Processing = false;
					return false;
				}else if( UploadResponse == "" ){
					alert("不明なエラーが発生しました");
					xmlRequest.abort();
					Processing = false;
					return false;
				}
				
				//1枚目のアップロード終了時
				if( Uploading == 1 ){
					document.getElementById("UploaderCurtainTextBox").style.display = "block";
					document.getElementById("CompleteUploadURLTextBox").value = UploadResponse;
					document.getElementById("PreviewImage" + Uploading + "").innerHTML = "";
				}
				
				//2枚目以降のアップロード終了時
				else if( 2 <= Uploading ){
					var EndedUploadResponse = document.getElementById("CompleteUploadURLTextBox").value;
					document.getElementById("CompleteUploadURLTextBox").value = UploadResponse + "\n" + EndedUploadResponse;
					document.getElementById("PreviewImage" + Uploading + "").innerHTML = "";
				}
				
				//アップロードに続きがある場合の処理
				if( Uploading < ImageBoxNum ){
					GoUpload();
				}
				
				//アップロード終了時の表示
				else if( Uploading == ImageBoxNum ){
					document.getElementById("ResultButtonArea").style.display = "block";
					document.getElementById("UploaderCurtainMessage").innerHTML = "アップロードが完了しました";
					localStorage.setItem("LocalDeleteKey",document.getElementById("DeleteKeyBox").value);
				}
			}
			
			return;
		}
	}
	
	//ベーシックアップローダー
	else{
	
		if( document.getElementById("UploadMedia").value == "" ){
			alert("画像を選択してください");
			Processing = false;
			return false;
		}else{
			document.getElementById("UploaderCurtain").style.display = "block";
			document.getElementById("UploaderCurtainMessage").innerHTML = "アップロード中です…";
			document.ImageUploader.submit();
			return false;
		}
	}
}

//ベーシックアップローダー用結果表示
function ResultFlushBasic(){
	
	//アップロード中でなければ無視
	if( !Processing ){ return; }
	
	//iframeの内容を取得（互換性注意）
	UploadResponse = decodeURIComponent(document.getElementById("BasicResultBoxID").contentDocument.body.innerHTML);
	UploadResponse = UploadResponse.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g,''); //preタグ除去用
	
	//アップロードエラー時の処理
	if( UploadResponse == "100" ){
		alert("この画像はアップロードできません : 容量制限");
		Processing = false;
		return false;
	}else if( UploadResponse == "200" ){
		alert("この画像はアップロードできません : 不明形式");
		Processing = false;
		return false;
	}else if( UploadResponse == "" ){
		alert("不明なエラーが発生しました");
		Processing = false;
		return false;
	}
	
	//結果表示
	document.getElementById("UploaderCurtainTextBox").style.display = "block";
	document.getElementById("ResultButtonArea").style.display = "block";
	document.getElementById("CompleteUploadURLTextBox").value = UploadResponse;
	document.getElementById("UploaderCurtainMessage").innerHTML = "アップロードが完了しました";
	localStorage.setItem("LocalDeleteKey",document.getElementById("DeleteKeyBox").value);

	return;

}
//URLBoxの表示・非表示
function ToggleURLBox(){

	if( !OpenURLBox ){
		document.getElementById("URLBox").style.marginTop = "-225px";
		document.getElementById("URLBoxInner").style.boxShadow = "0 0 10px #000";
		OpenURLBox = true;
	}else{
		document.getElementById("URLBox").style.marginTop = "-45px";
		document.getElementById("URLBoxInner").style.boxShadow = "0 0 0 #000";
		OpenURLBox = false;
	}
	
	return;

}

//URLBoxのコピー
function CopyURLBox( BoxID ){

	//Internet Explorer
	if( window.clipboardData ){
		URLBoxTextR = document.getElementById(BoxID).value.replace(/\n/g,"\r\n");
		window.clipboardData.setData("Text",URLBoxTextR);
		alert("URLBoxをコピーしました");
		return;
	}else{
		document.getElementById(BoxID).select();
		try{
			document.execCommand('copy');
			alert("URLBoxをコピーしました");
		}catch(err){
			alert("コピーできません");
		}
		document.getElementById(BoxID).select(0);
	}
	
	return;
	
}

//URLボックスにURLを代入
function urlbox( ub_cmd ){

	if( !OpenURLBox ){
		ToggleURLBox();
	}

	switch( ub_cmd ){
		case "clear":
			document.getElementById("urlbox-textarea").value = "";
			localStorage.setItem("SavedURLBox","");
		break;

		default:
			before_urlbox_textarea = document.getElementById("urlbox-textarea").value;
			document.getElementById("urlbox-textarea").value = ub_cmd + "\n" + before_urlbox_textarea;
			localStorage.setItem("SavedURLBox",document.getElementById("urlbox-textarea").value);
		break;
	}

	return;
}