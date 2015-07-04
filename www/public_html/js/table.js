$(document).bind("pagecreate", function () {
    $(".ui-table-cell-label").parent().contents().filter(function () {
        return this.nodeType == 3;
    }).wrap("<b class=\"ui-table-cell-title\"></b>");
});

var widthSet = [];

$(document).on("click", "th", function (event) {
    var id = $(event.target).text();
    orderTable(id, widthSet);
});

$(document).on("click", "td .ui-table-cell-label", function (event) {
    $.mobile.loading('show');
    $(this).animate({color: "#C52837"}, function() {
        orderTable($(event.target).text(), widthSet);
    });
});

$(window).on("orientationchange", function (event) {
    promoteSelectedColumn(event.orientation);
});

$(document).one("pageshow", "#page-1", function () {
    $(this).find("tr").eq(0).find("th").each(function () {
        widthSet.push($(this).width() + "px");
    });
});

$(document).one("pagebeforeshow", "#page-1", function () {
    $(window).orientationchange();
});

function orderTable(id, widthSet) {
    var orderBy = $("#order-by");
    var orderDir = $("#order-direction");

    if (orderBy.val().toLowerCase() === id.toLowerCase()) {
        if (orderDir.val() === "asc") {
            orderDir.val("desc");
        } else {
            orderDir.val("asc");
        }
    } else {
        orderBy.val(id);
        orderDir.val("asc");
    }

    $('body').jtable(jsonData, {
        orderBy: orderBy.val(),
        asc: orderDir.val() === "asc",
        transition: "none",
        widthSet: widthSet
    });

    $.ajax({
        url: "session.php",
        type: "get",
        data: {
            order: orderBy.val(),
            dir: orderDir.val()
        }
    });

    $(window).orientationchange();
}

var swapIndex = -1;
var otherIndex = -1;

function promoteSelectedColumn(orientation) {
    var width = (orientation === "landscape") ? screen.height : screen.width;

    if (width < 400 && orientation == "portrait") {
        $('table thead tr').each(function () {
            var sel = $(this).find('.ui-highlight').eq(0);
            var other = $(this).children().not(".ui-highlight").eq(0);
            if (other) {
                swapIndex = sel.index();
                otherIndex = other.index();
                sel.detach().insertBefore(other);
            }
        });

        if (swapIndex >= 0) {
            $('table tbody tr').each(function () {
                var sel = $(this).children().eq(swapIndex);
                var other = $(this).children().eq(otherIndex);
                if (other) {
                    swapIndex = sel.index();
                    sel.detach().insertBefore(other);
                }
            });
        }
    } else if (width < 400 && orientation == "landscape") {
        if (swapIndex >= 0) {
            $('table thead tr').each(function () {
                var sel = $(this).children().eq(otherIndex);
                var other = $(this).children().eq(swapIndex);
                sel.detach().insertAfter(other);
            });

            $('table tbody tr').each(function () {
                var sel = $(this).children().eq(otherIndex);
                var other = $(this).children().eq(swapIndex);
                sel.detach().insertAfter(other);
            });

            swapIndex = -1;
            otherIndex = -1;
        }
    }
}