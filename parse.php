<?php
/**
 * @attention be careful using, it will parse PHP code directed!
 */
$ac = isset($_GET['_ac']) ? $_GET['_ac'] : 'display';
$obj = new Test();
$obj->$ac();

class Test
{
    private $_source = '';

    public function display() {
        echo <<< EOF
<!DOCTYPE html>
<html>
    <head>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <style>
//            #inputField {float: left;}
//            #inputField textarea {height: 0em; width: 30em; border: 1px solid #ccc;}
            #outputField {height: 20em; width: 60em; border: 1px solid #ccc;}
        </style>
    </head>

    <body>
        <div id="type">
            <label><input class="type" type="radio" value="execSource" name="type" checked="checked" />execute php code</label>
            <label><input class="type" type="radio" value="jsonDecode" name="type" />json decode</label>
            <label><input class="type" type="radio" value="base64Decode" name="type" />base64 decode</label>
        </div>
        <div id="inputField"><textarea rows="15" cols="120"></textarea></div>
        <div id="outputField"></div>
        <div id="command"><input id="cleanBtn" type="button" value="clean" /></div>
    </body>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#inputField textarea').keyup(function (e) {
                if (e.keyCode == 13 && (e.metaKey || e.altKey)) {
                    $.ajax({
                        type: 'POST',
                        url: '/parse.php?_ac='+$('input[name=type]:checked').val(),
                        data: {source: $(this).val()},
                        complete: function(xhr) {
                            $('#outputField').append(xhr.responseText);
                        }
                    });
                }
            });
            $('#cleanBtn').click(function() {
                $('#outputField').empty();
            });
        });
    </script>
</html>
EOF;
    }

    private function _check() {
        $source = isset($_POST['source']) && !empty($_POST['source']) ? trim($_POST['source']) : '';
        if (!$source) {
            echo 'empty source!<br />';
            exit;
        }
        if (get_magic_quotes_gpc()) {
            $source = stripslashes($source);
        }

        $this->_source = $source;
    }

    public function execSource() {
        $this->_check();

        eval($this->_source);
    }

    public function jsonDecode() {
        $this->_check();

        print_r(json_decode($this->_source, true));
    }

    public function base64Decode() {
        $this->_check();

        $parsedStr = base64_decode($this->_source, true);
        if (strpos($parsedStr, '&') !== false) {
            parse_str($parsedStr, $parsedStr);
        }

        print_r($parsedStr);
    }
}
