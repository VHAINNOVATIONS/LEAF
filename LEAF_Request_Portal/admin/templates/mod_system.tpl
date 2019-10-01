<style>
input {
    min-width: 300px;
}
.item label {
    font-size: 120%;
    font-weight: bold;
    width: 250px;
}

.item {
    padding-bottom: 16px;
}
</style>

<h2 id="progress" style="color: red; text-align: center">
</h2>

<div style="width: 70%; margin: auto">
    <div style="border: 2px solid black; margin: 4px; background-color: white; padding: 16px">
        <div class="item">
        <label for="heading">Title of LEAF site:&nbsp;</label>
        <input id="heading" type="text" title="" />
        </div>

        <div class="item">
        <label for="subHeading">Facility Name:&nbsp;</label>
        <input id="subHeading" type="text" title="" />
        </div>

        <div class="item">
        <label for="timeZone">Time Zone:&nbsp;</label>
        <select id="timeZone">
            <!--{foreach from=$timeZones item=tz}-->
                <option value="<!--{$tz}-->"><!--{$tz}--></option>
            <!--{/foreach}-->
        </select>
        </div>

        <div class="item">
        <label for="requestLabel">Label for "Request":&nbsp;</label>
        <input id="requestLabel" type="text" title="" />
        </div>

        <div class="item">
            <label for="leafSecureContent">LEAF Secure Status:&nbsp;</label>
            <span id="leafSecureStatus">Loading...</span><br />
        </div>

<br /><br />
        <div class="item">
        <label for="subHeading">Import Tags [<a href="#" title="Groups in the Org. Chart with any one of these tags will be imported for use">?</a>]:&nbsp;</label>
            <span style="font-style: italic">
            <!--{foreach from=$importTags item=importTag}-->
                <!--{$importTag}--><br />
            <!--{/foreach}-->
            </span>
        </div>

        <fieldset>
            <legend>Advanced Settings</legend>
            <div class="item">
                <label for="siteType">Type of Site:&nbsp;</label>
                <select id="siteType">
                    <option value="standard">Standard</option>
                    <option value="national_primary">Nationally Standardized Primary</option>
                    <option value="national_subordinate">Nationally Standardized Subordinate</option>
                </select>
            </div>

            <div class="item siteType national_primary" style="display: none">
                    <label for="national_linkedSubordinateList">Nationally Standardized Subordinate Sites:<br /><span style="font-size: 12px; font-weight: normal">The first site in the list should be a TEST site.<br />URLs must end with a trailing slash.</span></label>
                    <textarea id="national_linkedSubordinateList" cols="50" rows="5"></textarea>
            </div>

            <div class="item siteType national_subordinate" style="display: none">
                    <label for="national_linkedPrimary">Nationally Standardized Primary Site<span style="font-size: 12px; font-weight: normal">URLs must end with a trailing slash.</span></label>
                    <input id="national_linkedPrimary" type="text" />
            </div>
        </fieldset>

        <button class="buttonNorm" onclick="saveSettings();" style="float: right"><img src="../../libs/dynicons/?img=media-floppy.svg&w=32" alt="save icon" /> Save</button>
        <br /><br />
    </div>
</div>

<!--{include file="site_elements/generic_xhrDialog.tpl"}-->
<!--{include file="site_elements/generic_confirm_xhrDialog.tpl"}-->

<script type="text/javascript">
var CSRFToken = '<!--{$CSRFToken}-->';

function saveSettings()
{
    $.when(
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/heading',
                data: {heading: $('#heading').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/subHeading',
                data: {subHeading: $('#subHeading').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/requestLabel',
                data: {requestLabel: $('#requestLabel').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/timeZone',
                data: {timeZone: $('#timeZone').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/siteType',
                data: {siteType: $('#siteType').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/national_linkedSubordinateList',
                data: {national_linkedSubordinateList: $('#national_linkedSubordinateList').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            }),
            $.ajax({
                type: 'POST',
                url: '../api/?a=system/settings/national_linkedPrimary',
                data: {national_linkedPrimary: $('#national_linkedPrimary').val(),
                    CSRFToken: '<!--{$CSRFToken}-->'},
                success: function(res) {
                }
            })
         ).then(function() {
        	 $('#progress').html('Settings saved.');
        	 $('#progress').fadeIn(10, function() {
                 $('#progress').fadeOut(2000);
        	 });
         });
}

function renderSiteType() {
    $('.siteType').css('display', 'none');
    switch($('#siteType').val()) {
        case 'national_primary':
            $('.national_primary').css('display', 'inline');
            break;
        case 'national_subordinate':
            $('.national_subordinate').css('display', 'inline');
            break;
        default:
            break;
    }
}

function renderSettings(res) {
    var query = new LeafFormQuery();
    query.setRootURL('../');
    query.addTerm('categoryID', '=', 'leaf_secure');

    for(var i in res) {
        $('#' + i).val(res[i]);
        if(i == 'leafSecure') {
            if(res[i] >= 1) { // Certified
                query.addTerm('stepID', '=', 'resolved');
                query.join('recordResolutionData');
                query.onSuccess(function(data) {
                    var mostRecentID = null;
                    var mostRecentDate = 0;
                    for(var i in data) {
                        if(data[i].recordResolutionData.lastStatus == 'Approved'
                            && data[i].recordResolutionData.fulfillmentTime > mostRecentDate) {
                            mostRecentDate = data[i].recordResolutionData.fulfillmentTime;
                            mostRecentID = i;
                        }
                    }
                    $('#leafSecureStatus').html('<span style="font-size: 120%; padding: 4px; background-color: green; color: white; font-weight: bold">Certified</span> <a class="buttonNorm" href="../index.php?a=printview&recordID='+ mostRecentID +'">View details</a>');
                });
                query.execute();
            }
            else { // Not certified
                query.addTerm('stepID', '!=', 'resolved');
                query.onSuccess(function(data) {
                    if(data.length == 0) {
                        $('#leafSecureStatus').html('<span style="font-size: 120%; padding: 4px; background-color: red; color: white; font-weight: bold">Not Certified</span> <a class="buttonNorm" href="../report.php?a=LEAF_start_leaf_secure_certification">Start Certification Process</a>');
                    }
                    else {
                        var recordID = data[Object.keys(data)[0]].recordID;
                        $('#leafSecureStatus').html('<span style="font-size: 120%; padding: 4px; background-color: red; color: white; font-weight: bold">Not Certified</span> <a class="buttonNorm" href="../index.php?a=printview&recordID='+ recordID +'">Check Certification Progress</a>');
                    }
                });
                query.execute();
            }
        }
    }
    renderSiteType();
}

var dialog, dialog_confirm;
$(function() {
	dialog = new dialogController('xhrDialog', 'xhr', 'loadIndicator', 'button_save', 'button_cancelchange');
    dialog_confirm = new dialogController('confirm_xhrDialog', 'confirm_xhr', 'confirm_loadIndicator', 'confirm_button_save', 'confirm_button_cancelchange');

    $.ajax({
        type: 'GET',
        url: '../api/system/settings',
        cache: false
    })
    .then(function(res) {
        renderSettings(res)
    });

    $('#siteType').on('change', function() {
        renderSiteType();
    });
});

</script>
