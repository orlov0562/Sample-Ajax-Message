<?php
    header('Content-Type: text/html; charset=utf-8');
    
    $messages = file_exists('messages1.txt')
              ? array_reverse(file('messages1.txt'))
              : array()  
    ;
    
    if (!empty($_POST['is_ajax']))
    {
        session_cache_limiter('nocache');
        header('Expires: ' . gmdate('r', 0));
        header('Content-type: application/json');
        
        $newTextItem = isset($_POST['newTextItem'])
                       ? trim($_POST['newTextItem'])
                       : ''
        ;
        
        $lastTextItemId = isset($_POST['lastTextItemId'])
                          ? intval($_POST['lastTextItemId'])
                          : 0;
        
        $cToken = count($messages) ? md5(trim($messages[count($messages)-1])) : '';                          
        $token = isset($_POST['token']) ? $_POST['token'] : '';

        if ($token!=$cToken) die(json_encode(array('error'=>'Database flushed, please reload the page')));
        if (!$newTextItem) die(json_encode(array('error'=>'Empty message')));
        if ($lastTextItemId > (count($messages)+1)) {die(json_encode(array('error'=>'Internal error, please reload the page')));}
        
        if ($newTextItem=='clean') {
            file_put_contents('messages1.txt','');
            die(json_encode(array('clean'=>'yes')));            
        }

        $newTextItem = date('H:i:s').'] '.strip_tags($newTextItem);

        $lastItemsText = '<p>'.$newTextItem.'</p>';
        
        for ($i=0; $i<(count($messages)-$lastTextItemId); $i++) {
            $lastItemsText .= '<p>'.$messages[$i].'</p>';
        }
        
        file_put_contents('messages1.txt', $newTextItem.PHP_EOL, FILE_APPEND);
        
        array_unshift($messages, $newTextItem);
        
        $answer = array(
            'appendText'    => $lastItemsText, 
            'lastTextItemId' => count($messages) ? count($messages) : 0,
            'token' => md5(trim($messages[count($messages)-1])),
        );
        
        die(json_encode($answer));
    }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title>Message example</title>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>

<body>
    <form>
    Text: <input type="text" value="" id="text">
    <input type="submit" value="Add message" id="submit">
    | Page loaded <?php echo date('H:i:s');?>
    </form>
    <hr />
    
    <div id="chat"><?php
        if (!empty($messages)) echo '<p>'.implode('</p><p>',$messages).'</p>';
    ?></div>
    
    <script>
        var lastTextItemId = <?php echo count($messages) ? count($messages) : 0; ?>;
        var token = '<?php echo count($messages) ? md5(trim($messages[count($messages)-1])) : null ?>';
        $('#submit').click(function(){
            $.ajax({
                type: "POST",
                url: "index1.php",
                data: { 
                        is_ajax:'yes', 
                        newTextItem: $('#text').val(),
                        lastTextItemId: lastTextItemId,
                        token:token 
                },
                dataType: "json",
                success: function (data) {
                    if (data.error) {
                        alert(data.error);
                    } else if (data.clean) {
                        $('#chat').html('<p style="color:grey"><small>log cleaned</small></p>');
                        $('#text').val('');
                        lastTextItemId = 0;
                        token = null;
                    } else if (data.appendText && data.lastTextItemId) {
                        if (!token) token = data.token; 
                        if (data.token!=token) {
                            alert('Database flushed, please reload the page');
                            return false;
                        }
                        $('#chat').prepend(data.appendText);
                        $('#text').val('');
                        lastTextItemId = data.lastTextItemId;
                    }
                }
            });
            
            return false;
        });
    </script>
</body>
</html>