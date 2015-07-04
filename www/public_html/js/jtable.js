(function ($) {

    $.fn.jtable = function(data, options) {

        var settings = $.extend({
            elementsPerPage: 20,
            headings: "director,year,title",
            orderBy: "title",
            asc: true,
            transition: "fade",
            widthSet: ["","",""],
            pageSelect: null,
            callback: function() {}
        }, options);

        sortJSON(data, settings.orderBy, settings.asc);

        var keys = settings.headings.split(",");
        var numRows = data.length;
        var numPages = Math.max(1, Math.ceil(numRows / settings.elementsPerPage));

        var pages = '';
        for (var i = 1; i <= numPages; i++) {
            var page = "page-"+ i,
                prevPage = (i == 1 ? "" : "#page-" + (i - 1)),
                nextPage = (i > numPages - 1 ? "" : "#page-" + (i+1));

            var thead = '<tr class="ui-bar-d">';
            for (var k = 0; k < keys.length; k++) {
                thead += '<th';
                if (settings.orderBy === keys[k]) {
                    thead += ' class="ui-highlight"';
                }
                if (settings.widthSet[k]) {
                    thead += ' style="width:'+settings.widthSet[k]+'"';
                }
                thead += '>' + keys[k] + '</th>';
            }
            thead += '</tr>';

            var tbody = '';
            var currRow = settings.elementsPerPage * (i - 1);
            var rowCap = Math.min(currRow + settings.elementsPerPage, numRows);
            for (var row = currRow; row < rowCap; row++) {
                tbody += '<tr';
                if (data[row].pick) {
                    tbody += ' class="picked"';
                }
                tbody += '>';
                for (var k = 0; k < keys.length; k++) {
                    tbody += '<td';
                    if (settings.orderBy === keys[k]) {
                        tbody += ' class="ui-highlight"';
                    }
                    tbody += '>' + data[row][keys[k]] + '</td>';
                }
                tbody += '</tr>';
            }

            pages +=
                '<div data-role="page" title="Page '+i+'" id="page-'+i+'" data-prev="'+prevPage+'" data-next="'+nextPage+'">' +
                    '<div data-role="content">' +
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

            $("#page-" + i).remove();
        }

        this.append($(pages));

        if (settings.pageSelect) {
            var options = '';
            for (var i = 1; i <= numPages; i++) {
                options += '<option value="page-' + i + '">Page ' + i + '</option>';
            }
            $(settings.pageSelect).append($(options));
        }

        $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: settings.transition});

        settings.callback.call(this); // brings the scope to the callback

        return this;
    };

    function sortJSON(data, prop, asc) {
        data.sort(function(a, b) {
            if (asc) return (a[prop] > b[prop]) ? 1 : ((a[prop] < b[prop]) ? -1 : 0);
            else return (b[prop] > a[prop]) ? 1 : ((b[prop] < a[prop]) ? -1 : 0);
        });
    }

}(jQuery));