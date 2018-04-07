// 组件库
var component = {
    controlPart: {
        // 图片搜索框
        searchBox: '<div class="form-group"><input type="text" class="form-control first-part-input" id="searchInput" placeholder="输入图片名和组名搜索图片">' +
        ' <a class="btn btn-default" onclick="searchImage()">搜索</a></div> <form> <div class="form-group" style="margin-left:100px;"><input type="text" class="form-control" name="group_name" id="group-name" placeholder="请先填写图片分组名"></div> <input style="display: none;" type="file" name="image" onchange="uploadImg(this)" class="form-control file-box top-upload-files" multiple><button style="margin-bottom: 15px;" type="button" class="btn btn-success" onclick="pretendClickUpload(this)">上传新图片</button></form>'
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
