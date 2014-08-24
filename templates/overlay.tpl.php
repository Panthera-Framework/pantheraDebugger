<script type="text/javascript">
function b64_to_utf8(str)
{
	return decodeURIComponent(escape(window.atob(str)));
}

var w = window.open('','pantheraDebugger','height=400,width=1000,scrollbars=1');
w.document.write(b64_to_utf8('<?=$popupContent?>'));
w.document.close();
</script>