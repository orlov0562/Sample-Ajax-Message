<?php
    header('Content-Type: text/html; charset=utf-8');
    if (!empty($_POST['is_ajax']))
    {
        session_cache_limiter('nocache');
        header('Expires: ' . gmdate('r', 0));
        header('Content-type: application/json');
        
        $newTextItem = isset($_POST['newTextItem']) ? $_POST['newTextItem'] : '';
        
        $newTextItem = date('H:i:s').'] '.strip_tags(trim($newTextItem));

        file_put_contents('messages2.txt', $newTextItem.PHP_EOL, FILE_APPEND);
        
        die(json_encode(array('result'=>$newTextItem)));
    }
    
    $messages = file_exists('messages2.txt')
              ? file('messages2.txt')
              : array()  
    ;
   
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
        $('#submit').click(function(){
            $.ajax({
                type: "POST",
                url: "index2.php",
                data: { 
                        is_ajax:'yes', 
                        newTextItem: $('#text').val(),
                },
                dataType: "json",
                success: function (data) {
                    if (data.result) {
                        $('#chat').prepend('<p>'+data.result+'</p>');
                        $('#text').val('');
                    }
                }
            });
            
            return false;
        });
    </script>
</body>
</html>