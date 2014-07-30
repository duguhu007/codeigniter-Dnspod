<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo $title;?></title>
<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.css">
<script type="text/javascript" src="/assets/bootstrap/js/jquery-1.9.1.js"></script>
</head>
<style>
<!--
.domains {
	width: 1000px;
}
-->
</style>
<script>
	function checkAll(obj){
		var cks=document.getElementsByName("ids[]");
		for(var i=0;i<cks.length;i++){
			cks[i].checked=obj.checked;
		}
	}
	function ad()
	{
		var domains = $("#domains").val();
		$.post("/domain/add_domains",{"domains":domains},function(msg){
			alert(msg);
			location.reload();
		});
	}

	function cip()
	{
		var ids = document.getElementsByName("ids[]");
		var ip1 = document.getElementById("ip1").value;
		var ip2 = document.getElementById("ip2").value;
		var str = "";
		for(var i=0;i<ids.length;i++)
		{
			if(ids[i].checked)
			{
				str += ids[i].value+",";
			}
		}
		if(str!="")
		{
			str = str.substr(0,str.length-1);
			$.post("<?php echo $formaction;?>",{"ids":str,"ip1":ip1,"ip2":ip2},function(msg){
					alert(msg);
					location.reload();
				});
		}
	}
	
</script>
<body class="">
	<div class="container">
	<div class="alert alert-block alert-error fade in">
	
	</div>
	<header style="height: 25px;">
		<a href="<?php echo $change->url;?>"><?php echo $change->name;?></a>
	</header>
	<label class="checkbox">
     <input type="checkbox" id="all" onclick="checkAll(this)">ALL
     </label>
		<form action="<?php echo $formaction;?>" method="post">
			<div class="domains form-actions">
			<?php
			if(!empty($list->domains)):
			$r = 0;
			foreach ( $list->domains as $key => $val ):
				?>
				<?php 
				if ($r<4)
				{
					$r++;
				}
				else
				{
					$r=0;
				}
				?>
				<label class="checkbox inline">
				<input type="checkbox" name="ids[]" value="<?php echo $val->id;?>"> <?php echo $val->name?> &nbsp;&nbsp;<?php echo $r==4?'<br>':'';?>
				</label>
			<?php
			endforeach;
			endif;
			?>
			</div>
			<div class="form-actions domains">
				@ip:<input type="text" name="ip1" id="ip1" class="input-medium">
				*ip:<input type="text" name="ip2" id="ip2" class="input-medium">
				<button type="button" class="btn" onclick="cip()">perform</button>
			</div>
			<div class="pagination">
			  <ul>
			    <li><a href="<?php echo $prev;?>">Prev</a></li>
			    <li><a href="<?php echo $next;?>">Next</a></li>
			  </ul>
			</div>
		</form>
		<div class="addDomains input-append">
			<label>add Domain:</label>
			<input type="text" name="domains" id="domains" class="span4"> <button type="button" class="btn" onclick="ad()">addDomains</button>
		</div>
	</div>
</body>
</html>