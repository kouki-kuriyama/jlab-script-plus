//各変数の初期化
var EnableFileAPI;
var PhotoReader;
var DragDrop;

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
	}
}

//ファイルドロップ
function onFileDrop(e){
	if( EnableFileAPI ){
		e.stopPropagation();
		e.preventDefault();
		FileLoad(e.dataTransfer.files[0]);
	}
}

//指定された画像を読み込んでINPUTに代入する
function FileLoad(RawFileData){

	//データの存在確認
	if( !RawFileData ){ return false; }
	
	//簡易確認
	if( !RawFileData.type.match(/image\/jpeg/) && !RawFileData.type.match(/image\/png/) && !RawFileData.type.match(/image\/gif/)){
		alert("アップロードできる形式ではありません");
		return;
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
		document.getElementById("ImageBase64N").value = BinaryBase64;
		
		//ドラッグアンドドロップ有効
		DragDrop = true;

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

//クリアする
function AllClear(){
	
	if( DragDrop ){
		document.getElementById("UploadMedia").style.display = "inline";
		document.getElementById("LoadedFileName").innerHTML = "";
		document.getElementById("ImageBase64N").value = "";
		document.getElementById("Preview").innerHTML = "";
		RawFileData = "";
		BinaryBase64 = "";
	}
	
	document.ImageUploader.reset();
	return;
	
}