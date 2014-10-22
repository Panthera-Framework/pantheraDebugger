<!--<div id="queryStringJSON" style="width: 100%; height: 80%;"></div>-->
<link rel="stylesheet" type="text/css" href="webroot/jstree/dist/themes/default/style.min.css">
<script type="text/javascript" src="webroot/jstree/dist/jstree.min.js"></script>

<?php
$data = $tabData;
unset($data['title']);
unset($data['template']);
?>

<div id="jstree_demo_div">
	<!-- in this example the tree is populated from inline HTML -->
	<ul>
		<?php
		if ($data)
		{
			foreach ($data as $key => $value)
			{
				print('<li id="__sep__' .$key. '">'.$key);

				if (is_array($value))
				{
					print('<ul id="parent__sep__' .$key. '"><li>...</li></ul>');
				}

				print('</li>');
			}
		}
		?>
	</ul>
</div>

<script type="text/javascript">
	$(function () { $('#jstree_demo_div').jstree({
			'core': {
				'data' : {
					'url' : function (node) {
						return 'index.php?jsonRequest=getData&tabName=request&fileName=<?=$fileName;?>&path=/';
					},
					'data' : function (node) {
						return { 'id' : node.id };
					}
				},

				"check_callback" : true
			}
		});
	});
</script>
