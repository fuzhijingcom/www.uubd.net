//加载数据渲染表格的方法
function initTable(data) {
    if (data) {
        var imageTable = document.getElementById('imageTbody');
        imageTable.innerHTML = "";

        removeTableP();
        var tableHead = document.getElementById('tableHead');
        tableHead.innerHTML = component.tableTitle;
        tableHead.title = 'image-list';

        var tableField = component.tableField;

        for (var i = 0; i < data.length; ++i) {
            var TR = document.createElement('tr');

            for (var j = 0; j < tableField.length; ++j) {
                var td = document.createElement('td');
                var str;
                if (tableField[j] == 'preview') {
                    str = '<img src="' + SITE_URL + '/' + data[i]['relative_url'] + '">';
                } else if (tableField[j] == 'options') {
                    var file_str = '<form><div class="form-group" style="display: none;"><input value="' + data[i]['id'] + '" name="id" type="text" class="form-control" placeholder="图片id"></div><input style="display: none;" type="file"  name="image" class="form-control file-box" onchange="updateImg(this)">';
                    var file_str_end = '</form>';
                    str = file_str + '<button type="button" class="btn btn-success" onclick="pretendClick(this)">更换图片</button> <button type="button" class="btn btn-danger" onclick="removeImg(this, ' + data[i]['id'] + ')" data-toggle="confirmation">删除</button>' + file_str_end;
                } else {
                    str = data[i][tableField[j]];
                }
                td.innerHTML = str;
                TR.appendChild(td);
            }

            imageTable.appendChild(TR);
        }

        // call the tablesorter plugin
        $("table").tablesorter({debug: false, widgets: ['zebra']});
    } else {
        console.log('welcome to other images management!');
    }
}


/**
 * 上传多张图片()
 *
 * 目前有问题，group_name 不能动态获取
 */
function multipleImgUpload() {
    $('#ssi-upload2').ssi_uploader({
        url: URL + '/uploadImg',
        data: {group_name: '未定义'},
        locale: 'zh_CN',
        preview: false,
        onUpload: function () {
            searchImage();
        },
        beforeUpload: function () {
            var info = $('#group-name').val();
            alert(info);
        }
    });
}
