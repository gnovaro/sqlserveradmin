<?php
/**
 * @author Gustavo Novaro
 * @version 1.0.1
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
      </div>
    </nav>
<div class="container-fluid">
    <form method="post">
        <div class="form-group">
            <textarea name="query" required="required" cols="120" placeholder="Add your SQL query heare. Ex: SELECT * FROM table" class="form-control"><?php echo $query;?></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Ejecutar</button>
        </div>
    </form>
    <?php
    if(!empty($query))
    {
        $result = odbc_exec($connection,$query);
    }
    ?>
    <?php
    // Get Data From Result
    if(!empty($result)):
        $rows_count = odbc_num_rows ($result);
    ?>
    <div>
        <strong>Registros:</strong> <?php echo $rows_count;?>
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
        </table>
    </div><!--./table-responsive-->
    <?php
        // Free Result
        odbc_free_result($result);
    endif;
    // Close Connection
    odbc_close($connection);
    ?>
</div><!--./container-fluid-->
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
