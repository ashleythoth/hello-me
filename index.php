<?php

// vars
$servername = "localhost";
$username = "dbuser";
$password = "dbpass";
$dbname = "dbname";
$api[0] = "chatgptkey1";
$api[1] = "chatgptkey1";
$first_contact = "Hello";
$loop_limit = "20";
$chat = array();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 

// first contact sql entry
$sql = "INSERT INTO chat (id, message, timestamp)
VALUES (NULL, '".$first_contact."', '".date("Y-m-d H:i:s")."')";

if ($conn->query($sql) === TRUE) {
  //echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

// talk to itself
for ( $a = 0; $a < $loop_limit; $a++ ) {
	
	$chat[0] = $first_contact;
	
	$bot = $a%2+1;
	echo "ChatGPT ".$bot.": ".$chat[$a]."<br><br>";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer '.$api[$a%2].'',
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"model\": \"gpt-3.5-turbo\",\n    \"messages\": [\n      {\n        \"role\": \"user\",\n        \"content\": \"".$chat[$a]."\"\n      }\n    ]\n  }");
	
	$response = curl_exec($ch);
	
	curl_close($ch);
	
	$ans = json_decode($response);
	
	$chat[$a+1] = $ans->choices[0]->message->content;
	
	$sql = "INSERT INTO chat (id, message, timestamp)
			VALUES (NULL, '".$chat[$a+1]."', '".date("Y-m-d H:i:s")."')";

	if ($conn->query($sql) === TRUE) {
		//echo "New record created successfully";
	} else {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
	
	// pause to avoid exceeding quota
	sleep(3);
	
}

?>
