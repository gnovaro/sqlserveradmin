<?php
/**
 * @author Gustavo Novaro
 * @author Agustín Garcia
 * @version 1.0.7
 */
define('APP_NAME','SQL Server Admin');
require('config.php');
$connection = odbc_connect("Driver={SQL Server};Server=$server;Database=$database;", $user, $password);

$query = !empty($_POST['query']) ? $_POST['query'] : null;

function exportCSV($data)
{
    $filename = 'export_'.date('Ymd_His').'.csv';
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment;filename='.$filename);
    $out = fopen('php://output', 'w');
    foreach($data as $line) {
        if(is_array($line)){
            fputcsv($out, $line);
        }
    }
    fclose($out);
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'export')
{
    $i = 0;
    $result = @odbc_exec($connection,$query);
    if(!empty($result)) {
        $columns = odbc_num_fields($result);
        for ($j=1; $j<= $columns; $j++)
        {
            $header[] = odbc_field_name ($result, $j );
        }
        $data[] = $header;

        while($row = odbc_fetch_array($result)){
            $data[] = $row;
        }
        exportCSV($data);
    }
    die;
}

//Show tables
$query_tables = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG='$database' ORDER BY TABLE_NAME";
$result_tables = @odbc_exec($connection,$query_tables);
while($row = odbc_fetch_object($result_tables)){
    $tables[] = $row->TABLE_NAME;
}
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
        box-shadow:0 2px 5px 0 rgba(0,0,0,0.16), 0 2px 10px 0 rgba(0,0,0,0.12);
    }
    .table-hover>tbody>tr:hover{
        background-color: #E8F5E9 !important;
    }
    .table-list-container {
        border: solid 1px #f6f6f6;
        max-height: 200px;
        overflow-y: scroll;
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
            <div class="col-md-3 table-list-container">
            <?php
            if(!empty($tables)):
            ?>
                <?php
                foreach($tables as $table):
                ?>
                <div>
                    <a class="set-query" data-table="<?php echo $table;?>"><span class="fa fa-table"></span> <?php echo $table;?></a>
                </div>
                <?php
                endforeach;
                ?>
            <?php
            endif;
            ?>
            </div>
            <div class="col-md-9">
                <div id="queryEditor"><?php echo $query;?></div>
                <input type="hidden" name="query" id="query">
            </div>
        </div>
        <div class="clearfix"></div>
        <br>
        <div class="form-group">
            <div class="col-md-6">
                <button type="button" class="btn btn-sm btn-primary" onclick="setAction('query')">Run <span class="glyphicon glyphicon-play"></span></button> (Run query with F9)
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-success btn-xs pull-right" onclick="setAction('export')">Export csv <span class="fa fa-file-excel-o"></span></button>
            </div>
        </div>
        <input type="hidden" name="action" id="action" value="query">
    </form>
    <div class="clearfix"></div>
    <?php
    if(!empty($query))
    {
        $result = @odbc_exec($connection,$query);
    }
    ?>
    <?php
    // Get Data From Result
    if(!empty($result)):
        $rows_count = odbc_num_rows($result);
        $columns = odbc_num_fields($result);
    ?>
    <div>
        <strong>Rows count:</strong> <?php echo $rows_count;?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <?php
            for ($j=1; $j<= $columns; $j++):
                $key = odbc_field_name ($result, $j );
            ?>
                <th><?php echo $key;?></th>
            <?php
            endfor;
            ?>
        </tr>
        </thead>
        <tbody>
            <?php
            while($row = odbc_fetch_array($result)):
            ?>
            <tr>
                <?php
                foreach($row as $key => $val):
                ?>
                <td><?php echo $val;?></td>
                <?php
                endforeach;
                ?>
            </tr>
            <?php
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

        $( ".set-query" ).on('click', function( event ) {
            var table = $(this).attr('data-table');
            var editor = ace.edit('queryEditor');
            query = editor.getValue();
            if(query === '')
                editor.setValue("SELECT * FROM "+table);                
            else
                editor.setValue(query + "\nSELECT * FROM "+table);
        });
	});
</script>
<script src="asset/SQLAdmin.js"></script>
</body>
</html>
