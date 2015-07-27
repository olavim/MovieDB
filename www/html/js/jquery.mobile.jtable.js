(function ($) {

    var defaultSettings = {
        elementsPerPage: 20,
        headings: "director,year,title",
        id: "id",
        orderBy: "title",
        asc: true,
        pageSelect: null
    };

    var methods = {
        init: function() {
            return init.apply(this, arguments);
        },
        refresh: function() {
            return refresh.apply(this, arguments);
        }
    };

    $.fn.jtable = function(data) {
        if (methods[data]) {
            return methods[data].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return methods.init.apply(this, arguments);
        }
    };

    function init(data, options) {
        var settings = defaultSettings;

        if (typeof options === 'object') {
            settings = $.extend(defaultSettings, options);
        }

        data = sortJSON(data, settings.orderBy, settings.asc);

        this.data("json", data);
        this.data("settings", settings);
        this.data("widthSet", []);

        var pages = buildTable(settings, data, []);
        $(".jtable-page").remove();
        this.append($(pages));

        var _this = this;
        $(document).one('pageshow', '.jtable-page', function() {
            var widthSet = [];
            $(this).find("th").each(function () {
                widthSet.push($(this).width() + "px");
            });
            _this.data("widthSet", widthSet);
        });

        $(document).on('pagebeforeshow', '.jtable-page', function() {
            $(this).find(':jqmData(role="viewselect")').val(settings.elementsPerPage);
            $(this).find(':jqmData(role="viewselect")').selectmenu("refresh");
        });

        return this;
    }

    function refresh(options) {
        var settings = $.extend(this.data("settings"), options);
        var data = this.data("json");

        data = sortJSON(data, settings.orderBy, settings.asc);

        var pages = buildTable(settings, data, this.data("widthSet"));
        $(".jtable-page").remove();
        this.append($(pages));

        this.data("json", data);
        this.data("settings", settings);

        return this;
    }

    function buildTable(settings, data, widthSet) {
        settings.elementsPerPage = parseInt(settings.elementsPerPage);

        var keys = settings.headings.split(",");
        var numRows = data.length;
        var numPages = Math.max(1, Math.ceil(numRows / settings.elementsPerPage));

        var pages = '';
        for (var i = 1; i <= numPages; i++) {
            var page = "page-" + i,
                prevPage = (i == 1 ? "" : "#page-" + (i - 1)),
                nextPage = (i > numPages - 1 ? "" : "#page-" + (i + 1));

            // thead
            var thead = '<tr class="ui-bar-d">';
            for (var k = 0; k < keys.length; k++) {
                thead += '<th';
                if (settings.orderBy === keys[k]) {
                    thead += ' class="ui-highlight"';
                }
                if (widthSet[k]) {
                    thead += ' style="width:' + widthSet[k] + '"';
                }
                thead += '>' + keys[k] + '</th>';
            }
            thead += '</tr>';

            // tbody
            var tbody = '';
            var currRow = settings.elementsPerPage * (i - 1);
            var rowCap = Math.min(currRow + settings.elementsPerPage, numRows);

            var row = currRow;
            Object.keys(data).forEach(function(key) {
                tbody += '<tr data-id="' + data[key]['id'] + '"';
                if (data[key]['picked']) {
                    tbody += ' class="picked"';
                }
                tbody += '>';
                for (var k = 0; k < keys.length; k++) {
                    tbody += '<td';
                    if (settings.orderBy === keys[k]) {
                        tbody += ' class="ui-highlight"';
                    }
                    value = data[key]['data'][keys[k]];
                    value = value ? value : '&nbsp;';
                    tbody += '>' + value + '</td>';
                }
                tbody += '</tr>';

                return ++row == rowCap;
            });

            // page
            pages +=
                '<div data-role="page" class="jtable-page" data-name="Page ' + i + '" id="page-' + i + '" data-prev="' + prevPage + '" data-next="' + nextPage + '">' +
                    '<div role="main" class="ui-content">' +
                        '<div class="select-view-contain">' +
                            '<div class="ui-field-contain">' +
                                '<label for="select-view-' + i + '">Rows per page:</label>' +
                                '<select data-theme="a" data-role="viewselect" id="select-view-' + i + '" data-mini="true">' +
                                    '<option value="20">20</option>' +
                                    '<option value="50">50</option>' +
                                    '<option value="100">100</option>' +
                                    '<option value="' + numRows + '">All</option>' +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                        '<table data-role="table" class="table-stripe ui-responsive ui-shadow ui-body-d">' +
                            '<thead>' +
                                thead +
                            '</thead>' +
                            '<tbody>' +
                                tbody +
                            '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>';
        }
        return pages;
    }

    function sortJSON(data, prop, asc) {
        sorted = [];
        Object.keys(data).sort(function(a, b) {
            aProp = data[a]['data'][prop];
            bProp = data[b]['data'][prop];
            var al = (typeof aProp === 'string' || aProp instanceof String) ? aProp.toLowerCase() : aProp;
            var bl = (typeof bProp === 'string' || bProp instanceof String) ? bProp.toLowerCase() : bProp;
            if (asc) return (al > bl) ? 1 : ((al < bl) ? -1 : 0);
            else return (bl > al) ? 1 : ((bl < al) ? -1 : 0);
        }).forEach(function(key) {
            sorted.push(data[key]);
        });

        return sorted;
    }

}(jQuery));