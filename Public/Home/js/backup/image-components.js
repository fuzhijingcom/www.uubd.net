// 组件库
var component1 = {
    controlPart: {
        // 图片搜索框
        searchBox: '<div class="form-group">' +

        '<select class="form-control" name="select-image-group-name" id="select-image-group" onchange="searchImage()">' +
        '<option value="" selected="selected">选择图片分组</option>' +
        '</select> ' +

        '<input type="text" class="form-control first-part-input" id="searchInput" placeholder="输入图片名和组名搜索图片">' +
        ' <a class="btn btn-default" onclick="searchImage()">搜索</a></div>' +

        '<form>' +
        '<div class="form-group" style="margin-left:100px;">' +
        ' <label for="group-name">上传图片分组名</label>' +
        '</div>' +
        ' <div class="form-group">' +
        ' <input type="text" class="form-control" name="group_name" id="group-name" placeholder="请选择或填写图片分组名" onchange="popImageSwiftList(0)" onfocus="popImageSwiftList(1)">' +

        '<ul title="pop" class="close-flag image-ul-exchange" id="image-group-ul" style="visibility: hidden;">' +

        '<li title="pop" onclick="fillContent(this)">商城-首页</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-公用</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-框架专区</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-太阳镜</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-运动专区</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-配镜攻略</li>' +
        '<li title="pop" onclick="fillContent(this)">商城-新品上市</li>' +

        '</ul>' +


        // '<select class="form-control" id="location" onchange="searchNotice()">' +
        // '<option value="汇总">所在位置(汇总)</option>' +
        // '<option value="总仓" selected="selected">总仓</option>' +
        // '<option value="新天地门店">新天地门店</option>' +
        // '<option value="广大门店">广大门店</option>' +
        // '<option value="南亭门店">南亭门店</option>' +
        // '<option value="科贸门店">科贸门店</option>' +
        // '<option value="退换品">退换品</option>' +
        // '</select>' +

        '</div>' +
        ' <input style="display: none;" type="file" name="image" onchange="uploadImg(this)" class="form-control file-box top-upload-files" multiple>' +
        '<button style="margin-bottom: 15px;" type="button" class="btn btn-success" onclick="pretendClickUpload(this)">上传新图片</button>' +
        '</form>'
    },

    tableTitle: '<tr>' +
    '<th>图片名</th>' +
    '<th>图片预览</th>' +
    '<th>分组名</th>' +
    '<th>相对URL</th>' +
    '<th>绝对URL</th>' +
    '<th>操作项</th>' +
    '</tr>',

    tableField: [
        'img_name',
        'preview',
        'group_name',
        'relative_url',
        'absolute_url',
        'options'
    ]

};
