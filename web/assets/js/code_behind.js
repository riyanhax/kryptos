var Diagram = MindFusion.Diagramming.Diagram;
var Glasseffect = MindFusion.Diagramming.GlassEffect;

var diagram;

$(document).ready(function(){
	diagram = Diagram.create($('#diagram')[0]);
	diagram.getNodeEffects().push(new Glasseffect());
	diagram.setAllowInplaceEdit(true);

	var nodeList = MindFusion.Diagramming.NodeListView.create($('#nodeList')[0]);
	nodeList.setTargetView($('diagram')[0]);
	initNodeList(nodeList,diagram);

});

function initNodeList(nodeList,diagram){
	var shapes = ["Database","Document","File","DiskStorage","Arrow3"];
	for(var i = 0; i < shapes.length; i++){
		var node  = new MindFusion.Diagramming.ShapeNode(diagram);
		node.setText(shapes[i]);
		node.setShape(shapes[i]);
		nodeList.addNode(node,shapes[i]);
	}

}


function saveDiagram()
{
	 var canvas = document.getElementById("diagram");
	var img    = canvas.toDataURL("image/png");
	window.location.href=img;
}
function loadDiagram(){
	var diagramString = localStorage.getItem('jsdiagram');
	alert(diagramString);
	diagram.fromJson(diagramString);
}