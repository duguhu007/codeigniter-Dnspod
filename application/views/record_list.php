<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Customer Service Center</title>
<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.css">
<script src="/assets/bootstrap/js/bootstrap.js" type="text/javascript"></script>
</head>
<style>
<!--
.domains {
	width: 1000px;
}
-->
</style>
<body class="">
	<div class="container">
	<form action="<?php echo $formaction;?>" method="post">
	<?php foreach ($arr as $key=>$val):?>
		<div id="<?php echo $val->domain->id;?>">
		<h1><?php echo $val->domain->name;?></h1>
		-----------------------------------------------------------------------
		<br/>
				<div class="records">
					<?php foreach($val->records as $k=>$v):?>
					<input type="checkbox" name="record[id][<?php echo $val->domain->id;?>][]" value="<?php echo $v->id;?>"> [<?php echo $v->name;?>]->[<?php echo $v->line;?>]->[<?php echo $v->type;?>] &nbsp;&nbsp;&nbsp;&nbsp;
					<input type="hidden" name="record[name][<?php echo $v->id;?>][]" value="<?php echo $v->name;?>">
					<input type="text" name="record[ip][<?php echo $v->id;?>][]" value="<?php echo $v->value;?>">
					<input type="hidden" name="record[line][<?php echo $v->id;?>][]" value="<?php echo $v->line;?>">
					<input type="hidden" name="record[type][<?php echo $v->id;?>][]" value="<?php echo $v->type;?>">
					<br>
					<?php endforeach;?>
				</div>
			
		</div>
	<?php endforeach;?>
	<button type="submit">update</button>
	</form>
	</div>
</body>
</html>