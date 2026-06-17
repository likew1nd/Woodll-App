/**
 * Created by Administrator on 2019/5/3.
 */
var __tableResizeTimer = null;
var __lastTableHeight = 0;
$(window).resize(function() {
    clearTimeout(__tableResizeTimer);
    __tableResizeTimer = setTimeout(function () {
        var $table = $('#tb_departments');
        if (!$table.length || !$table.data('bootstrap.table')) {
            return;
        }
        var height = Math.max($(window).height() - 100, 320);
        if (height === __lastTableHeight) {
            return;
        }
        __lastTableHeight = height;
        $table.bootstrapTable('resetView', {
            height: height
        });
    }, 150);
});

(function () {
    function getBodyTable($el) {
        var $wrap = $el.closest('.bootstrap-table');
        var $bodyTable = $wrap.find('.fixed-table-body > table').first();
        return $bodyTable.length ? $bodyTable : $wrap.find('table').last();
    }

    function syncSelectedRows($table) {
        $table.find('tbody tr[data-index]').each(function () {
            var $row = $(this);
            var checked = $row.find('td.bs-checkbox input[name="btSelectItem"]').prop('checked') === true;
            $row.toggleClass('selected', checked);
        });
    }

    function syncSelectAll($table) {
        var $items = $table.find('tbody tr[data-index]:visible td.bs-checkbox input[name="btSelectItem"]:enabled');
        var checkedCount = $items.filter(':checked').length;
        $table.closest('.bootstrap-table')
            .find('th.bs-checkbox input[name="btSelectAll"]')
            .prop('checked', $items.length > 0 && checkedCount === $items.length);
    }

    function syncSelectionState($table) {
        if ($table && $table.length) {
            syncSelectedRows($table);
            syncSelectAll($table);
        }
    }

    document.addEventListener('click', function (event) {
        var cell = event.target.closest && event.target.closest('.bootstrap-table th.bs-checkbox');
        if (!cell) {
            return;
        }

        var input = cell.querySelector('input[name="btSelectAll"]');
        if (!input || input.disabled) {
            return;
        }

        var $table = getBodyTable($(cell));
        if (!$table.length || typeof $table.bootstrapTable !== 'function') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        if (event.stopImmediatePropagation) {
            event.stopImmediatePropagation();
        }

        $table.bootstrapTable(input.checked ? 'uncheckAll' : 'checkAll');
        syncSelectionState($table);
    }, true);

    $(document).on('check-all.bs.table uncheck-all.bs.table post-body.bs.table load-success.bs.table', 'table', function () {
        var $table = $(this);
        setTimeout(function () {
            syncSelectionState($table);
        }, 0);
    });
})();

$.validator.addMethod("minNumber",function(value, element){
    return this.optional(element) || /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/.test(value);  //金额,不允许货币格式
},"必须输入数字金额类型且最多支持到分单位");         //验证错误信息
$.validator.addMethod("chrnum", function(value, element) {
    var chrnum = /^([a-zA-Z0-9]+)$/;
    return this.optional(element) || (chrnum.test(value));
}, "只能输入数字和字母(字符A-Z, a-z, 0-9)");
