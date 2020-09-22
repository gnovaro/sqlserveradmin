<?php
/**
 * @author Gustavo Novaro
 * @version 1.0.9
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

//Tables
$query_tables = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG='$database' ORDER BY TABLE_NAME";
$result_tables = @odbc_exec($connection,$query_tables);
while($row = odbc_fetch_object($result_tables)){
    $tables[] = $row->TABLE_NAME;
}

//Views
$query_views = "SELECT TABLE_NAME FROM $database.INFORMATION_SCHEMA.VIEWS ORDER BY TABLE_NAME";
$result_views = @odbc_exec($connection,$query_views);
while($row = odbc_fetch_object($result_views)){
    $views[] = $row->TABLE_NAME;
}

//Procedures
$query_procedures = "SELECT ROUTINE_NAME
                    FROM $database.information_schema.routines
                    WHERE routine_type = 'PROCEDURE'
                    ORDER BY ROUTINE_NAME";
$result_procedures = @odbc_exec($connection,$query_procedures);
while($row = odbc_fetch_object($result_procedures)){
    $procedures[] = $row->ROUTINE_NAME;
}
?>
<!doctype html>
<html lang="en">
<head>
    <title><?php echo APP_NAME;?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="asset/sqladmin.css">
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
            <div>
                <label for="showTables">Tables</label>
                <input type="checkbox" id="showTables" name="showTables" value="show_tables" class="filtros" checked="checked">
                <label for="showViews">Views</label>
                <input type="checkbox" id="showViews" name="showViews" value="show_views" class="filtros" checked="checked">
                <label for="showSP">Stored procedures</label>
                <input type="checkbox" id="showSP" name="showSP" value="show_sp" class="filtros" checked="checked">
            </div>
            <div class="col-md-3 table-list-container">
                <div><!--tables-->
                <?php
                if(!empty($tables)):
                ?>
                    <?php
                    foreach($tables as $table):
                    ?>
                    <div class="tree-view-item">
                        <a class="set-query" data-table="<?php echo $table;?>"><span class="fa fa-table"></span> <?php echo $table;?></a>
                    </div>
                    <?php
                    endforeach;
                    ?>
                <?php
                endif;
                ?>
                </div><!--tables-->
                <div><!--views-->
                <?php
                if(!empty($views)):
                ?>
                    <?php
                    foreach($views as $view):
                    ?>
                    <div class="tree-view-item">
                        <a class="set-query" data-view="<?php echo $view;?>"><span class="fa fa-wpforms"></span> <?php echo $view;?></a>
                    </div>
                    <?php
                    endforeach;
                    ?>
                <?php
                endif;
                ?>
                </div><!--views-->
                <div><!--procedures-->
                <?php
                if(!empty($procedures)):
                ?>
                    <?php
                    foreach($procedures as $procedure):
                    ?>
                    <div class="tree-view-item">
                        <a class="set-procedure" data-procedure="<?php echo $procedure;?>"><span class="fa fa-file-code-o"></span> <?php echo $procedure;?></a>
                    </div>
                    <?php
                    endforeach;
                    ?>
                <?php
                endif;
                ?>
                </div><!--procedures-->
            </div>
            <div class="col-md-9">
                <div id="queryEditor"><?php echo $query;?></div>
                <input type="hidden" name="query" id="query">
                <div class="form-group buttons-wrapper">
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="setAction('query')">Run <span class="glyphicon glyphicon-play"></span></button> (Run query with F9)
            </div>
                    <div>
                        <button type="button" class="btn btn-success btn-xs pull-right" onclick="setAction('export')">Export csv <span class="fa fa-file-excel-o"></span></button>
        </div>
            </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <br>
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
    <?php if(!empty($columns)):?>
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
    <?php endif;?>
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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
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
        };

        //Set table
        $( ".set-query" ).on('click', function( event ) {
            var table = $(this).attr('data-table');
            var editor = ace.edit('queryEditor');
            query = editor.getValue();
            if(query === '')
                editor.setValue("SELECT * FROM "+table);
            else
                editor.setValue(query + "\nSELECT * FROM "+table);
        });
        //Set procedure
        $( ".set-procedure" ).on('click', function( event ) {
            var procedure = $(this).attr('data-procedure');
            var editor = ace.edit('queryEditor');
            query = editor.getValue();
            if(query === '')
                editor.setValue("EXECUTE "+procedure);
            else
                editor.setValue(query + "\nEXECUTE "+procedure);
        });

        $('.filtros').on('change', function(event) {
            var tipoFiltro = $(this).val();
            var checked = $(this).attr('checked');
            if(tipoFiltro === 'show_tables'){
                $('div > a[data-table]').toggle(checked);
            }
            if(tipoFiltro === 'show_views'){
                $('div > a[data-view]').toggle(checked);
            }
            if(tipoFiltro === 'show_sp'){
                $('div > a[data-procedure]').toggle(checked);
            }
        });
	});
</script>
<script src="asset/SQLAdmin.js"></script>
</body>
</html>
