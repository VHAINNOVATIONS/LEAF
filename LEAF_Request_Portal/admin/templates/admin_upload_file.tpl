<div style="width: 70%; margin: auto">
    <form id="record" enctype="multipart/form-data" action="ajaxIndex.php?a=uploadFile" method="post">
        <input name="CSRFToken" type="hidden" value="{$CSRFToken}" />
        <div id="file_control">Select file to upload: <input id="file" name="file" type="file" /></div>
        <div id="file_status" style="visibility: hidden; display: none; background-color: #fffcae; padding: 4px"><img src="../images/indicator.gif" alt="loading..." /> Uploading file...</div>
        <br /><br />Supported file types: <!--{foreach from=$fileExtensions item=extension}--><!--{$extension}--> <!--{/foreach}-->
    </form>
</div>
<script type="text/javascript">
$(function() {
    $('#file').on('change', function() {
        $('#file_control').css('visibility', 'hidden');
        $('#file_control').css('display', 'none');
        $('#file_status').css('visibility', 'visible');
        $('#file_status').css('display', 'block');
        $('#record').submit();
    });
});
</script>
