<html>
<head>
</head>
<body>

<?php
//データベースへの接続
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$connectpass = 'パスワード';
	$pdo = new PDO($dsn, $user, $connectpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//データベース内にテーブルを作成する
	$sql = "CREATE TABLE if not exists datatable (id INT AUTO_INCREMENT PRIMARY KEY,name char(32),comment TEXT,date DATETIME,password char(32));";
	$stmt = $pdo->query($sql);

//テーブル作成ができたか確認する
	//$sql = 'SHOW TABLES';
	//$result = $pdo ->query($sql);
	//foreach ($result as $row){
	//	echo $row[0];
	//	echo '<br>';
	//}

//意図した内容のテーブルが作成されているか確認する
//$sql ='SHOW CREATE TABLE datatable';
//	$result = $pdo -> query($sql);
//	foreach ($result as $row){
//		echo $row[1];
//	}
//	echo "<hr>";
	
//投稿フォームで編集したコメントを送る
	//もし投稿フォームの名前・コメント、隠した編集番号フォーム、すべてに値が入っていたら編集として扱う
	if((!empty($_POST['name'])) && (!empty($_POST['comment'])) && (!empty($_POST['editnumbers']))){

		//datatableの中の情報を選択、実行
		$sql = 'SELECT * FROM datatable';
		$stmt = $pdo->query($sql);

		//1行ごとに取り出す
		$results = $stmt->fetchAll();

		//1行ごとにループ処理
		foreach ($results as $row){
			//もしその行の投稿番号と、hiddenの編集番号が同じだったら
			if($row['id'] == $_POST['editnumbers']){

				//もしパスワードも入力され、編集されそうになったら「パスワードは変更できません。」と表示する
				if(!empty($_POST['toukoupassword'])){
					echo "編集しました。パスワードのみ編集できません。";
				}else{
					echo "編集しました。";
				}

				//入力したデータをupdateによって編集する
				$id = $_POST['editnumbers'];
				$name = $_POST['name'];
				$comment = $_POST['comment'];
				$date = date("YmdHis");

				$sql = 'update datatable set name=:name,comment=:comment,date=:date where id=:id';
				$stmt = $pdo->prepare($sql);
				$stmt -> bindParam(':name',$name, PDO::PARAM_STR);
				$stmt -> bindParam(':comment',$comment, PDO::PARAM_STR);
				$stmt -> bindParam(':date',$date, PDO::PARAM_STR);
				$stmt -> bindParam(':id', $id, PDO::PARAM_INT);
				$stmt -> execute();
			}
		}
		

	}elseif((!empty($_POST['name'])) && (!empty($_POST['comment']))){
		//新規投稿の場合
		//$id = $_POST['editnum'];
		

		//作成したテーブルに、insertを使ってデータを入力する

		$sql = $pdo->prepare("INSERT INTO datatable (name,comment,date,password) VALUES (:name,:comment,:date,:password)");
		$sql -> bindParam(':name',$name,PDO::PARAM_STR);
		$sql -> bindParam(':comment',$comment, PDO::PARAM_STR);
		$sql -> bindParam(':date',$date,PDO::PARAM_INT);
		$sql -> bindParam(':password',$password,PDO::PARAM_STR);

		$name = $_POST['name'];
		$comment = $_POST['comment'];
		$password = $_POST['toukoupassword'];
		$date = date("YmdHis");
		$sql -> execute();

//ここから削除時
	}elseif((!empty($_POST['deletenumber'])) && (!empty($_POST['deletepassword']))){
	//もし削除フォームに、削除対象番号とパスワードが入力されたら

		$deletenumber = $_POST['deletenumber'];
		$deletepassword = $_POST['deletepassword'];

		//datatableの中の情報を選択、実行
		$sql = 'SELECT * FROM datatable';
		$stmt = $pdo->query($sql);

		//1行ごとに取り出す
		$results = $stmt->fetchAll();

		//1行ごとにループ処理
		foreach ($results as $row){

			//もしその行の投稿番号と、削除対象番号が同じで、
			//なおかつその行のパスワードと入力されたパスワードが同じだったら
			if($row['id'] == $deletenumber && $row['password'] == $deletepassword ){

				//削除したい投稿番号を指定
				$id = $deletenumber;

				$sql = 'delete from datatable where id=:id';
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		if(empty($id)){
			echo "パスワードが間違っている、もしくは投稿時のパスワードの入力がなく削除できない投稿です。";
		}else{
			echo "削除しました。";
		}

//ここから編集機能
	//もし編集番号とパスワードが入力されたら
	}elseif(!empty($_POST['editnumber']) && !empty($_POST['editpassword'])){
		$editnumber = $_POST['editnumber'];
		$editpassword = $_POST['editpassword'];

		//datatableの中の情報を選択、実行
		$sql = 'SELECT * FROM datatable';
		$stmt = $pdo->query($sql);

		//1行ごとに取り出す
		$results = $stmt->fetchAll();

		//1行ごとにループ処理
		foreach ($results as $row){
			//もしその行の投稿番号と、編集対象番号が同じ、なおかつ
			//その行のパスワードと入力されたパスワードが同じだったら
			if($row['id'] == $editnumber && $row['password'] == $editpassword){

				//投稿フォームにその行の名前とコメントを送るために、変数にそれらを格納する
				$editname = $row['name'];
				$editcomment = $row['comment'];
				$editnumbers = $row['id'];
			}
		}
		if(empty($editname)){
			echo "パスワードが間違っている、もしくは投稿時にパスワードの入力がなく編集できない投稿です。";
		}
//投稿、削除、編集機能がいずれも機能しなかったが、何かしらの値がフォームに入力されたときは、「入力しなおしてください。」と表示する
	}elseif(empty($_POST['name']) && empty($_POST['comment']) && empty($_POST['toukoupassword']) && empty($_POST['deletenumber']) && empty($_POST['deletepassword']) && empty($_POST['editnumber']) && empty($_POST['editpassword'])){
	}else{
		echo "入力項目が間違っています。入力しなおしてください。";
	}
?>

<!--ここから投稿フォーム-->
	<form action = "mission_5-1.php" method = "post">
	<label for = "name"> 名前 </label>
	<input type = "text" id = "name" name = "name" placeholder = "名前を入力してください "value = 
		<?php
		if(!empty($editname)){
			echo $editname;
		}; 
		?>> <br>

	<label for = "comment"> コメント </label>
	<input type = "text" id = "comment" name = "comment" placeholder = "コメントを入力してください "value = 
		<?php
		if(!empty($editcomment)){
			echo $editcomment;
		};
		?>> <br>

	<label for = "toukoupassword"> パスワード </label>
	<input type = "password" id = "toukoupassword" name = "toukoupassword" placeholder = "パスワード">

	<input type = "hidden" id = "editnumbers" name = "editnumbers" value = 
		<?php
		if(!empty($editnumbers)){
			echo $editnumbers;
		};
		?>> <br>

	<button type="submit">送信 </button> <br>

	</form>

	<!--ここから削除フォーム-->
	<form action = "mission_5-1.php" method = "post">

	<label for "deletenumber"> 削除対象番号 </label>
	<input type = "number" id = "deletenumber" name = "deletenumber" placeholder = "1"> <br>

	<label for = "deletepassword"> パスワード </label>
	<input type = "password" id = "deletepassword" name = "deletepassword" placeholder = "パスワード"> <br>

	<button type = "delete"> 削除 </button> <br>

	</form>

	<!--ここから編集フォーム-->
	<form action = "mission_5-1.php" method = "post">

	<label for "editnumber"> 編集対象番号 </label>
	<input type = "number" id = "editnumber" name = "editnumber" placeholder = "1"> <br>

	<label for "editpassword"> パスワード </label>
	<input type = "password" id = "editpassword" name = "editpassword" placeholder = "パスワード"> <br>
	
	<button type = "submit"> 編集 </button> <br> 

	</form>

<?php
//入力したデータをselectによって表示する
	$sql = 'SELECT * FROM datatable';
	$stmt = $pdo->query($sql);
	$results = $stmt->fetchAll();
	foreach ($results as $row){
		echo $row['id'].',';
		echo $row['name'].',';
		echo $row['comment'].',';
		echo $row['date'].'<br>';
		echo "<hr>";
	}
?>
</body>
</html>
