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

        data = jQuery.parseJSON(data);

        sortJSON(data, settings.orderBy, settings.asc);

        var keys = settings.headings.split(",");
        var numRows = data.length;
        var numPages = Math.max(1, Math.ceil(numRows / settings.elementsPerPage));

        for (var i = 1; i <= numPages; i++) {

            // page
            var parent = $('<div data-role="page"></div>').attr({
                "id": "page-" + i,
                "data-prev": (i == 1 ? "" : "#page-" + (i-1)),
                "data-next": (i > numPages - 1 ? "" : "#page-" + (i+1))})
            .append(

            // page-content
            $('<div data-role="content"></div>')
            .append(

            // page-content-table
            $('<table data-role="table" class="table-stripe ui-responsive ui-shadow ui-body-d"></table>')
            .append(

            // page-content-table-thead
            $("<thead></thead>")
            .append(

            // page-content-table-thead-tr
            function() {
                var tr = $('<tr class="ui-bar-d"></tr>');

            // page-content-table-thead-tr-th
                for (var k = 0; k < keys.length; k++) {
                    var th = $("<th></th>");
                    if (settings.orderBy === keys[k]) {
                        th.addClass("ui-highlight");
                    }

                    if (settings.widthSet[k]) {
                        th.width(settings.widthSet[k]);
                    }

                    th.text(keys[k]);
                    tr.append(th);
                }

                return tr;
            }))
            .append(
            function() {

            // page-content-table-tbody
                var tbody = $("<tbody></tbody>");

            // page-content-table-tbody-tr
                var currRow = settings.elementsPerPage * (i - 1);
                var rowCap = Math.min(currRow + settings.elementsPerPage, numRows);
                for (var row = currRow; row < rowCap; row++) {
                    var tr = $("<tr></tr>");
                    if (data[row].pick) {
                        tr.addClass("picked");
                    }

            // page-content-table-tbody-tr-td
                    for (var k = 0; k < keys.length; k++) {
                        var td = $("<td></td>");
                        if (settings.orderBy === keys[k]) {
                            td.addClass("ui-highlight");
                        }

                        td.text(data[row][keys[k]]);
                        tr.append(td);
                    }

                    tbody.append(tr);
                }

                return tbody;
            }
            )));

            // remove remnants
            $("#page-" + i).remove();

            this.append(parent);
        }

        if (settings.pageSelect) {
            for (var i = 1; i <= numPages; i++) {
                $(settings.pageSelect).append('<option value="page-' + i + '">Page ' + i + '</option>');
            }
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