jQuery(document).ready(function () {
    var strjson = [{"href":"http://home.com","icon":"fa fa-home","text":"Home", "target": "_top", "title": "My Home"},{"icon":"fa fa-bar-chart-o","text":"Opcion2"},{"icon":"fa fa-cloud-upload","text":"Opcion3"},{"icon":"fa fa-crop","text":"Opcion4"},{"icon":"fa fa-flask","text":"Opcion5"},{"icon":"fa fa-map-marker","text":"Opcion6"},{"icon":"fa fa-search","text":"Opcion7","children":[{"icon":"fa fa-plug","text":"Opcion7-1","children":[{"icon":"fa fa-filter","text":"Opcion7-1-1"}]}]}];
    var iconPickerOpt = {cols: 4, searchText: "Buscar...", labelHeader: '{0} de {1} Pags.', footer: true};
 /*    var options = {
        hintCss: {'border': '1px dashed #13981D'},
        placeholderCss: {'background-color': 'gray'},
        opener: {
            as: 'html',
            close: '<i class="fa fa-minus"></i>',
            open: '<i class="fa fa-plus"></i>',
            openerCss: {'margin-right': '10px'},
            openerClass: 'btn btn-success btn-xs'
        }
    }; */

    $('#myEditor_icon').on('change', function(e) {
        $('.item-menu').val(e.icon);
    });
    var editor = new MenuEditor('myEditor', {listOptions: options, iconPicker: iconPickerOpt, labelEdit: 'Edit'});
    editor.setForm($('#frmEdit'));
    editor.setUpdateButton($('#btnUpdate'));

    $('#btnReload').on('click', function () {
        editor.setData(strjson);
    });
    $('#btnOut').on('click', function () {
        var str = editor.getString();
        $("#out").text(str);
    });
    $("#btnUpdate").click(function(){
        editor.update();
    });
    $('#btnAdd').click(function(){
        editor.add();
    });
});