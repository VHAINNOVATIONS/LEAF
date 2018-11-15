function makeDropdown(options, selected){
    var dropdownElement = '<select role="dropdown" style="width:100%; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box; width: -webkit-fill-available; width: -moz-available; width: fill-available;">';
    for(var i = 0; i < options.length; i++){
        if(selected === options[i]){
            dropdownElement += '<option value="' + options[i] + '" selected="selected">' + options[i] + '</option>';
        } else {
            dropdownElement += '<option value="' + options[i] + '">' + options[i] + '</option>';
        }
    }
    dropdownElement += '</select>';
    return dropdownElement;
}
function printTableInput(gridParameters, values, indicatorID, series){
    var gridBodyElement = '#grid_' + indicatorID + '_' + series + '_input > tbody';
    var gridHeadElement = '#grid_' + indicatorID + '_' + series + '_input > thead';
    var rows = values.cells !== undefined && values.cells.length > 0 ? values.cells.length : 1;
    var columns = gridParameters.length;
    var element = '';

    //fix for report builder
    //prevents duplicate table from being created on edit
    if($(gridHeadElement + ' > td:last').html() !== undefined){
        return 0;
    }

    //finds and displays column names
    for(var i = 0; i < columns; i++){
        $(gridHeadElement).append('<td>' + gridParameters[i].name + '</td>');
    }
    $(gridHeadElement).append('<td style="width: 17px;">&nbsp;</td>');

    //populates table
    for(var i = 0; i < rows; i++){
        $(gridBodyElement).append('<tr></tr>');
        for(var j = 0; j < columns; j++){
            var value = values.cells === undefined || values.cells[i] === undefined || values.cells[i][j] === undefined ? '' : values.cells[i][j];
            if(gridParameters[j].type === 'dropdown'){
                element = makeDropdown(gridParameters[j].options, value);
            } else if(gridParameters[j].type === 'textarea'){
                element = '<textarea style="overflow-y:auto; overflow-x:hidden; resize: none; width:100%; height: 50px; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box; width: -webkit-fill-available; width: -moz-available; width: fill-available;">'+ value +'</textarea>';
            }
            $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td>' + element + '</td>');
        }
        if(rows === 1) {
            $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveUp()" src="../libs/dynicons/?img=go-up.svg&w=16" title="Move line up" alt="Move line up" style="display: none; cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="deleteRow()" src="../libs/dynicons/?img=process-stop.svg&w=16" title="Delete line" alt="Delete line" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveDown()" src="../libs/dynicons/?img=go-down.svg&w=16" title="Move line down" alt="Move line down" style="display: none; cursor: pointer" /></td>');
        } else {
            switch (i) {
                case 0:
                    $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveUp()" src="../libs/dynicons/?img=go-up.svg&w=16" title="Move line up" alt="Move line up" style="display: none; cursor: pointer" /></br><img role="button" tabindex="0" onclick="deleteRow()" src="../libs/dynicons/?img=process-stop.svg&w=16" title="Delete line" alt="Delete line" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveDown()" src="../libs/dynicons/?img=go-down.svg&w=16" title="Move line down" alt="Move line down" style="cursor: pointer" /></td>');
                    break;
                case rows - 1:
                    $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveUp()" src="../libs/dynicons/?img=go-up.svg&w=16" title="Move line up" alt="Move line up" style="cursor: pointer" /></br><img role="button" tabindex="0" onclick="deleteRow()" src="../libs/dynicons/?img=process-stop.svg&w=16" title="Delete line" alt="Delete line" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveDown()" src="../libs/dynicons/?img=go-down.svg&w=16" title="Move line down" alt="Move line down" style="display: none; cursor: pointer" /></td>');
                    break;
                default:
                    $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveUp()" src="../libs/dynicons/?img=go-up.svg&w=16" title="Move line up" alt="Move line up" style="cursor: pointer" /></br><img role="button" tabindex="0" onclick="deleteRow()" src="../libs/dynicons/?img=process-stop.svg&w=16" title="Delete line" alt="Delete line" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveDown()" src="../libs/dynicons/?img=go-down.svg&w=16" title="Move line down" alt="Move line down" style="cursor: pointer" /></td>');
                    break;
            }
        }
    }
}
function addRow(gridParameters, indicatorID, series){
    var gridBodyElement = '#grid_' + indicatorID + '_' + series + '_input > tbody';
    $(gridBodyElement + ' > tr:last > td:last').find('[title="Move line down"]').css('display', 'inline');
    $(gridBodyElement).append('<tr></tr>');
    for(var i = 0; i < gridParameters.length; i++){
        if(gridParameters[i].type === 'textarea'){
            $(gridBodyElement + ' > tr:last').append('<td><textarea style="overflow-y:auto; overflow-x:hidden; resize: none; width:100%; height: 50px; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; box-sizing:border-box; width: -webkit-fill-available; width: -moz-available; width: fill-available;"></textarea></td>');
        } else if(gridParameters[i].type === 'dropdown'){
            $(gridBodyElement + ' > tr:last').append('<td>' + makeDropdown(gridParameters[i].options, null) + '</td>');
        }
    }
    $(gridBodyElement + ' > tr:last').append('<td><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveUp()" src="../libs/dynicons/?img=go-up.svg&w=16" title="Move line up" alt="Move line up" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="deleteRow()" src="../libs/dynicons/?img=process-stop.svg&w=16" title="Delete line" alt="Delete line" style="cursor: pointer" /></br><img role="button" tabindex="0" onkeydown="triggerClick();" onclick="moveDown()" style="display: none" src="../libs/dynicons/?img=go-down.svg&w=16" title="Move line down" alt="Move line down" style="cursor: pointer" /></td>');
}
// click function for 508 compliance
function triggerClick(){
    if(event.keyCode === 13){
        $(event.target).trigger('click');
    }
}
function deleteRow(){
    var row = $(event.target).closest('tr');
    var tbody = $(event.target).closest('tbody');
    switch(tbody.find('tr').length){
        case 1:
            alert('Cannot remove inital row.');
            break;
        case 2:
            row.remove();
            upArrows(tbody.find('tr'), false);
            downArrows(tbody.find('tr'), false);
            break;
        default:
            if(row.find('[title="Move line down"]').css('display') === 'none'){
                downArrows(row.prev(), false);
                upArrows(row.prev(), true);
            }
            if(row.find('[title="Move line up"]').css('display') === 'none'){
                upArrows(row.next(), false);
                downArrows(row.next(), true);
            }
            row.remove();
            break;
    }
}
function upArrows(row, toggle){
    if(toggle){
        row.find('[title="Move line up"]').css('display', 'inline');
    } else {
        row.find('[title="Move line up"]').css('display', 'none');
    }
}
function downArrows(row, toggle){
    if(toggle){
        row.find('[title="Move line down"]').css('display', 'inline');
    } else {
        row.find('[title="Move line down"]').css('display', 'none');
    }
}
function moveDown(){
    var row = $(event.target).closest('tr');
    var nextRowBottom = row.next().find('[title="Move line down"]').css('display') === 'none';
    var rowTop = row.find('[title="Move line up"]').css('display') === 'none';
    upArrows(row, true);
    if(nextRowBottom){
        downArrows(row, false);
        downArrows(row.next(), true);
    }
    if(rowTop){
        upArrows(row.next(), false);
    }
    row.insertAfter(row.next());
}
function moveUp(){
    var row = $(event.target).closest('tr');
    var prevRowTop = row.prev().find('[title="Move line up"]').css('display') === 'none';
    var rowBottom = row.find('[title="Move line down"]').css('display') === 'none';
    downArrows(row, true);
    if(prevRowTop){
        upArrows(row, false);
        upArrows(row.prev(), true);
    }
    if(rowBottom){
        downArrows(row.prev(), false);
    }
    row.insertBefore(row.prev());
}
function printTableOutput(gridParameters, values, indicatorID, series) {
    var gridBodyElement = '#grid_' + indicatorID + '_' + series + '_output > tbody';
    var gridHeadElement = '#grid_' + indicatorID + '_' + series + '_output > thead';
    var rows = values.cells === undefined ? 0 : values.cells.length;
    var columns = gridParameters.length;

    //finds and displays column names
    for(var i = 0; i < columns; i++){
        $(gridHeadElement).append('<td>' + gridParameters[i].name + '</td>');
    }
    if(rows === 0){
        $(gridBodyElement).append('<tr></tr>');
        for(var i = 0; i < columns; i++){
            $(gridBodyElement + ' > tr').append('<td></td>');
        }
    }

    //populates table
    for (var i = 0; i < rows; i++) {
        $(gridBodyElement).append('<tr></tr>');
        for (var j = 0; j < columns; j++) {
            var value = values.cells[i] === undefined || values.cells[i][j] === undefined ? '' : values.cells[i][j];
            $(gridBodyElement + ' > tr:eq(' + i + ')').append('<td>' + value + '</td>')
        }
    }
}