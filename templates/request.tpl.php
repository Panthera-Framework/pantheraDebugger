<div id="queryStringJSON" style="width: 100%; height: 80%;"></div>

<?php
$data = $tabData;
unset($data['title']);
unset($data['template']);
?>

<script type="text/javascript" >
        // create the editor
        var editor = new JSONEditor(document.getElementById("queryStringJSON"));

        // set json
        editor.set(<?php print(json_encode($data));?>);
        editor.expandAll();
</script>