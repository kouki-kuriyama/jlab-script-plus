<!--

//各変数の初期化
var xmlRequest = new XMLHttpRequest();
var EnableFileAPI;
var PhotoReader;
var DragDrop;
var BinaryData;
var DeleteKey;
var VersionNumber;

//バージョン情報を設定
VersionNumber = "jlab-script-plus Ver0.06 dev3";

//ドラッグアンドドロップ関数が使用できるか確認する
function CheckEnableFileAPI(){

	if( window.File && window.FileReader && UseDragDrop ){
		EnableFileAPI = true;
		PhotoReader = new FileReader();
	}else{
		EnableFileAPI = false;
		document.getElementById("UploaderMessage").innerHTML = "ファイルを選択してください";
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

//ファイルドロップ
function onFileDrop(e){
	if( EnableFileAPI ){
		e.stopPropagation();
		e.preventDefault();
		document.getElementById("DragDropCurtainT").innerHTML = "取り込み中";
		FileLoad(e.dataTransfer.files[0]);
	}
}

//指定された画像を読み込んでINPUTに代入する
function FileLoad(RawFileData){

	//データの存在確認
	if( !RawFileData ){
		document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
		return false;
	}
	
	//簡易確認
	if( !RawFileData.type.match(/image\/jpeg/) && !RawFileData.type.match(/image\/png/) && !RawFileData.type.match(/image\/gif/)){
		document.getElementById("DragDropCurtainT").innerHTML = "取り込みに失敗";
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",2000);
		alert("アップロードできる形式ではありません");
		return false;
	}
	
	//ファイルを読み込む
	PhotoReader.onloadend = function(){

		//Base64形式を取り出す
		BinaryBase64 = PhotoReader.result;

		//ファイル名を表示する
		document.getElementById("UploadMedia").style.display = "none";
		document.getElementById("LoadedFileName").innerHTML = RawFileData.name;
		
		//プレビューを表示する
		if( RawFileData.size < 3145728 ){
			Preview_img.src = BinaryBase64;
		}else{
			document.getElementById("Preview").innerHTML = "<span style=\"color:#666;\">3MBを超えている画像のプレビューは表示されません<br>アップロードボタンを押してください</span>";
		}
		
		//INPUTに代入する
		BinaryData = BinaryBase64;
		
		//ドラッグアンドドロップ有効
		DragDrop = true;
		
		//メッセージ表示
		document.getElementById("DragDropCurtainT").innerHTML = "取り込み完了";
		if( !NextUploader ){ window.scroll(0,0); }
		setTimeout("document.getElementById('DragDropCurtain').style.top = '-200px';",1000);

	};
	
	//URLスキーム形式で取得する
	PhotoReader.readAsDataURL(RawFileData);
	document.getElementById("Preview").innerHTML = "";
	
	//プレビュー用HTMLを作成する
	if( RawFileData.size < 3145728 ){
		var Preview_img = document.createElement("img");
		document.getElementById("Preview").appendChild(Preview_img);
	}

}

//読み込んだ画像をクリアする
function AllClear(){
	
	if( DragDrop ){
		document.getElementById("UploadMedia").style.display = "inline";
		document.getElementById("LoadedFileName").innerHTML = "";
		document.getElementById("Preview").innerHTML = "";
		RawFileData = "";
		BinaryData = "";
		BinaryBase64 = "";
	}
	
	DragDrop = false;
	document.ImageUploader.reset();
	return;
	
}

//URLボックスの表示・非表示
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

}

//URLボックスにURLを代入
function urlbox( ub_cmd ){

	if( !OpenURLBox ){
		ToggleURLBox();
	}

	switch( ub_cmd ){
		case "clear":
			document.getElementById("urlbox-textarea").value = "";
		break;

		default:
			before_urlbox_textarea = document.getElementById("urlbox-textarea").value;
			document.getElementById("urlbox-textarea").value = ub_cmd + "\n" + before_urlbox_textarea;
		break;
	}

	return;
}

//画像をアップロード
//D&Dの場合はAjaxで送信する
function ImageUploading(){

	//アップロードタスクCookieの変更
	document.cookie = "UploadTask=Ready";

	if( DragDrop ){
	
		//ファイルチェック
		if( BinaryData == "" ){
			alert("ファイルを選択してください");
			return false;
		}
	
		document.getElementById("UploaderCurtain").style.display = "block";
		DeleteKey = document.getElementById("DeleteKeyBox").value;
		xmlRequest.onreadystatechange = function(){
			if( xmlRequest.readyState == 4 ){
				document.cookie = "Result=" + xmlRequest.responseText + "";
				location.href = "./upload.php";
				return;
			}
		}
		xmlRequest.open("POST","./upload.php",true);
		xmlRequest.setRequestHeader("content-type","application/x-www-form-urlencoded;charset=UTF-8");
		xmlRequest.send("type=dd&DeleteKey=" + DeleteKey + "&Image=" + BinaryData + "");
	}else{
	
		if( document.getElementById("UploadMedia").value == "" ){
			alert("ファイルを選択してください");
			return false;
		}else{
			document.ImageUploader.submit();
		}
	}
}