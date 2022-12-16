$(function() {
    var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)') || 0)[2];
    var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
    var $tbody = $('#list');
    $(window).on('hashchange', list).trigger('hashchange');
    $('#table').tablesorter();
    $('#table').on('click', '.delete', function(data) {
        $.post("", {
            'do': 'delete',
            file: $(this).attr('data-file'),
            xsrf: XSRF
        }, function(response) {
            list();
        }, 'json');
        return false;
    });

    // file upload stuff
    $('#file_drop_target').on('dragover', function() {
        $(this).addClass('drag_over');
        return false;
    }).on('dragend', function() {
        $(this).removeClass('drag_over');
        return false;
    }).on('drop', function(e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        $.each(files, function(k, file) {
            uploadFile(file);
        });
        $(this).removeClass('drag_over');
    });
    $('input[type=file]').change(function(e) {
        e.preventDefault();
        $.each(this.files, function(k, file) {
            uploadFile(file);
        });
    });

    function uploadFile(file) {
        var folder = decodeURIComponent(window.location.hash.substr(1));
        if (file.size > MAX_UPLOAD_SIZE) {
            var $error_row = renderFileSizeErrorRow(file, folder);
            $('#upload_progress').append($error_row);
            window.setTimeout(function() {
                $error_row.fadeOut();
            }, 5000);
            return false;
        }

        var $row = renderFileUploadRow(file, folder);
        $('#upload_progress').append($row);
        var fd = new FormData();
        fd.append('file_data', file);
        fd.append('file', folder);
        fd.append('xsrf', XSRF);
        fd.append('do', 'upload');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '?');
        xhr.onload = function() {
            $row.remove();
            list();
        };
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                $row.find('.progress').css('width', (e.loaded / e.total * 100 | 0) + '%');
            }
        };
        xhr.send(fd);
    }

    function renderFileUploadRow(file, folder) {
        return $row = $('<div/>')
            .append($('<span class="fileuploadname" />').text((folder ? folder + '/' : '') + file.name))
            .append($('<div class="progress_track"><div class="progress"></div></div>'))
            .append($('<span class="size" />').text(formatFileSize(file.size)))
    };

    function renderFileSizeErrorRow(file, folder) {
        return $row = $('<div class="error" />')
            .append($('<span class="fileuploadname" />').text('Error: ' + (folder ? folder + '/' : '') + file.name))
            .append($('<span/>').html(' file size - <b>' + formatFileSize(file.size) + '</b>' +
                ' exceeds max upload size of <b>' + formatFileSize(MAX_UPLOAD_SIZE) + '</b>'));
    }

    function list() {
        var hashval = window.location.hash.substr(1);
        $.get('?do=list&file=' + hashval, function(data) {
            $tbody.empty();
            if (data.success) {
                $.each(data.results, function(k, v) {
                    $tbody.append(renderFileRow(v));
                });
                !data.results.length && $tbody.append('<tr><td class="empty" colspan=5>This folder is empty</td></tr>')
                data.is_writable ? $('body').removeClass('no_write') : $('body').addClass('no_write');
            } else {
                console.warn(data.error.msg);
            }
            $('#table').retablesort();
        }, 'json');
    }

    function renderFileRow(data) {
        var $link = $('<a class="name" />')
            .attr('href', data.is_dir ? '#' + encodeURIComponent(data.path) : './' + encodeURIComponent(data.path))
            .text(data.name);
        var allow_direct_link = <?php echo $allow_direct_link ? 'true' : 'false'; ?>;
        if (!data.is_dir && !allow_direct_link) $link.css('pointer-events', 'none');
        var $dl_link = $('<a/>').attr('href', '?do=download&file=' + encodeURIComponent(data.path))
            .addClass('download').text('download');
        var $delete_link = $('<a href="#" />').attr('data-file', data.path).addClass('delete').text('delete');
        var perms = [];
        if (data.is_readable) perms.push('read');
        if (data.is_writable) perms.push('write');
        if (data.is_executable) perms.push('exec');
        var $html = $('<tr />')
            .addClass(data.is_dir ? 'is_dir' : '')
            .append($('<td class="first" />').append($link))
            .append($('<td/>').attr('data-sort', data.is_dir ? -1 : data.size)
                .html($('<span class="size" />').text(formatFileSize(data.size))))
            .append($('<td/>').attr('data-sort', data.mtime).text(formatTimestamp(data.mtime)))
            .append($('<td/>').text(perms.join('+')))
            .append($('<td/>').append($dl_link).append(data.is_deleteable ? $delete_link : ''))
        return $html;
    }

    function formatTimestamp(unix_timestamp) {
        var m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var d = new Date(unix_timestamp * 1000);
        return [m[d.getMonth()], ' ', d.getDate(), ', ', d.getFullYear(), " ",
            (d.getHours() % 12 || 12), ":", (d.getMinutes() < 10 ? '0' : '') + d.getMinutes(),
            " ", d.getHours() >= 12 ? 'PM' : 'AM'
        ].join('');
    }

    function formatFileSize(bytes) {
        var s = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];
        for (var pos = 0; bytes >= 1000; pos++, bytes /= 1024);
        var d = Math.round(bytes * 10);
        return pos ? [parseInt(d / 10), ".", d % 10, " ", s[pos]].join('') : bytes + ' bytes';
    }
})