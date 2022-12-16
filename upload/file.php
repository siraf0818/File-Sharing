<?php
include('../controller/setting.php');
include('../layout/header.php');
?>

<div class="row mt-5">
    <div class="col-6 mt-1"><button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#uploadModal">
            Add Files
        </button></div>
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="uploadModalLabel">Upload Files</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center  justify-content-center">
                        <?php if ($allow_upload) : ?>
                            <div id="file_drop_target" class="mx-auto">
                                Drag Files Here To Upload
                                <b>or</b>
                                <input type="file" multiple>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <div id="upload_progress"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 mt-1 d-flex justify-content-end">
        <input type="text" id="searchInput" onkeyup="searchFunction()" placeholder="File search...">
    </div>
</div>
<table id="table" class="table mt-3">
    <thead class="table-dark">
        <tr>
            <th class="text-center">Name</th>
            <th class="text-center">Size</th>
            <th class="text-center">Modified</th>
            <th class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody id="list">
    </tbody>
</table>
<script src="../assets/js/popper.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script src="../assets/js/jquery.min.js"></script>
<script>
    function searchFunction() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("table");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    (function($) {
        $.fn.tablesorter = function() {
            var $table = this;
            this.find('th').click(function() {
                var idx = $(this).index();
                var direction = $(this).hasClass('sort_asc');
                $table.tablesortby(idx, direction);
            });
            return this;
        };
        $.fn.tablesortby = function(idx, direction) {
            var $rows = this.find('tbody tr');

            function elementToVal(a) {
                var $a_elem = $(a).find('td:nth-child(' + (idx + 1) + ')');
                var a_val = $a_elem.attr('data-sort') || $a_elem.text();
                return (a_val == parseInt(a_val) ? parseInt(a_val) : a_val);
            }
            $rows.sort(function(a, b) {
                var a_val = elementToVal(a),
                    b_val = elementToVal(b);
                return (a_val > b_val ? 1 : (a_val == b_val ? 0 : -1)) * (direction ? 1 : -1);
            })
            this.find('th').removeClass('sort_asc sort_desc');
            $(this).find('thead th:nth-child(' + (idx + 1) + ')').addClass(direction ? 'sort_desc' : 'sort_asc');
            for (var i = 0; i < $rows.length; i++)
                this.append($rows[i]);
            this.settablesortmarkers();
            return this;
        }
        $.fn.retablesort = function() {
            var $e = this.find('thead th.sort_asc, thead th.sort_desc');
            if ($e.length)
                this.tablesortby($e.index(), $e.hasClass('sort_desc'));

            return this;
        }
        $.fn.settablesortmarkers = function() {
            this.find('thead th span.indicator').remove();
            this.find('thead th.sort_asc').append('<span class="indicator">&darr;<span>');
            this.find('thead th.sort_desc').append('<span class="indicator">&uarr;<span>');
            return this;
        }
    })(jQuery);
    $(function() {
        var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)') || 0)[2];
        var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
        var $tbody = $('#list');
        $(window).on('hashchange', list).trigger('hashchange');
        $('#table').tablesorter();
        $('#table').on('click', '.delete', function(data) {
            if (confirm("Delete File?")) {
                $.post("", {
                    'do': 'delete',
                    file: $(this).attr('data-file'),
                    xsrf: XSRF
                }, function(response) {
                    list();
                }, 'json');
            }
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
            var $dl_link = $('<a class="text-center px-2"/>').attr('href', '?do=download&file=' + encodeURIComponent(data.path))
                .addClass('download').text('Download');
            var $delete_link = $('<a href="#" class="text-center px-2" />').attr('data-file', data.path).addClass('delete').text('Delete');
            var $html = $('<tr />')
                .addClass(data.is_dir ? 'is_dir' : '')
                .append($('<td class="first" />').append($link))
                .append($('<td class="text-center"/>').attr('data-sort', data.is_dir ? -1 : data.size)
                    .html($('<span class="size" />').text(formatFileSize(data.size))))
                .append($('<td class="text-center"/>').attr('data-sort', data.mtime).text(formatTimestamp(data.mtime)))
                .append($('<td class="text-center"/>').append($dl_link).append(data.is_deleteable ? $delete_link : ''))
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
</script>
</body>

</html>