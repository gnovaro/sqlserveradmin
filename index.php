<?php
/**
 * @author Gustavo Novaro
 * @version 1.0.4
 */
define('APP_NAME','SQL Server Admin');
require('config.php');
$connection = odbc_connect("Driver={SQL Server};Server=$server;Database=$database;", $user, $password);

$query = !empty($_POST['query']) ? $_POST['query'] : null;
?>
<!doctype html>
<html>
<head>
    <title><?php echo APP_NAME;?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
    #queryEditor {
        height: 200px;
    }
   </style>
</head>
<body>
<!-- Static navbar -->
<nav class="navbar navbar-default navbar-static-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"><?php echo APP_NAME;?></a>
    </div>
    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
        <li>
          <a href="#"><span class="fa fa-database" aria-hidden="true"></span> <?php echo $database;?></a>
        </li>
        </ul>
    </div>
  </div>
</nav>
<div class="container-fluid">
    <form id="frm-query" method="post">
        <div class="form-group">
            <div id="queryEditor"><?php echo $query;?></div>
            <!--<textarea name="query" id="query" required="required" cols="120" placeholder="Add your SQL query heare. Ex: SELECT * FROM table" class="form-control"><?php echo $query;?></textarea>-->
            <input type="hidden" name="query" id="query">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Run <span class="glyphicon glyphicon-play"></span></button> (Run query with F9)
        </div>
    </form>
    <?php
    if(!empty($query))
    {
        $result = @odbc_exec($connection,$query);
    }
    ?>
    <?php
    // Get Data From Result
    if(!empty($result)):
        $rows_count = odbc_num_rows ($result);
    ?>
    <div>
        <strong>Rows count:</strong> <?php echo $rows_count;?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <?php
            $i = 0;
            while($data[] = odbc_fetch_array($result)):
            ?>

            <?php
            //Encabezado
            if($i == 0):
            ?>
            <thead>
                <tr>
                    <?php
                    foreach($data[$i] as $key => $val):
                    ?>
                    <th><?php echo $key;?></th>
                    <?php
                    endforeach;
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            endif
            ?>

            <tr>
                <?php
                foreach($data[$i] as $key => $val):
                ?>
                <td><?php echo $val;?></td>
                <?php
                endforeach;
                ?>
            </tr>
            <?php
                $i++;
            endwhile;
            ?>
        </tbody>
        </table>
    </div><!--./table-responsive-->
    <?php
        // Free Result
        odbc_free_result($result);
    else:
    //Error messages
        echo odbc_errormsg();
    endif;
    // Close Connection
    odbc_close($connection);
    ?>
</div><!--./container-fluid-->
<!-- Latest compiled and minified JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ext-modelist.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ext-themelist.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/mode-sqlserver.js"></script>
<script>
	$( document ).ready(function() {
		var editor = ace.edit('queryEditor');
		editor.setTheme("ace/theme/sqlserver");
		editor.getSession().setMode("ace/mode/sqlserver");

		$( "#frm-query" ).on('submit', function( event ) {
			event.preventDefault();
			var editor = ace.edit('queryEditor');
		    $("#query").val(editor.getValue());
			this.submit();
		});

        window.onkeypress = function(e) {
            //IF keyCode == F9 submit
            console.log(e.keyCode);
            if(e.keyCode === 120) {
                $("#frm-query").submit();
            }
        }
	});
</script>
</body>
</html>
