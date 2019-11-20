<!DOCTYPE html>
<html>
<head>
	<title>Flow Charts</title>
	<script type="text/javascript">
		var TopLeft = 0;
	</script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" type="text/javascript"></script>
	
	
</head>
<body>
	<div style="width: 200px; height: 300px; position: absolute; left: 0px; right: 0px;">
		<canvas id = "nodeList" width="200"></canvas> 
	</div>
	<div style="position: absolute; left: 201px;right: 0px;top: 0px;bottom: 0px;overflow: auto;">
		<canvas id="diagram" width="2100" height="2100" >
				 This page required a browser that supprot html5 canvas element. 	
		</canvas>
		
	</div>
	<div style="position: absolute;left: 0px;top: 210px; right: 0px">
		<input type="button" value="Save" onclick="saveDiagram()">
	</div>
	<div style="position: absolute;left: 0px;top: 250px; right: 0px">
		<input type="button" value="Load" onclick="loadDiagram()">
		
	</div>
	<script src="MindFusion.Common.js" type="text/javascript"></script>
	<script src="MindFusion.Diagramming.js" type="text/javascript"></script>
	<script src="code_behind.js" type="text/javascript"></script>
</body>
</html>