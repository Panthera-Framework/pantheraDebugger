<div id="queryStringJSON" style="width: 100%; height: 80%;"></div>

<script type="text/javascript" >
        // create the editor
        var editor = new JSONEditor(document.getElementById("queryStringJSON"));

        // set json
        editor.set(<?php print(json_encode($tabData['inline']));?>);
        editor.expandAll();
</script>