var shifted = false;
var selectStart;
$(document).on('keyup keydown', function(e) {
    shifted = e.shiftKey;
});

$(document).bind("pagecreate", function () {
    $(".ui-table-cell-label").parent().contents().filter(function () {
        return this.nodeType == 3;
    }).wrap("<b class=\"ui-table-cell-title\"></b>");
});

$(document).bind("pageshow", function () {
    shifted = false;
    selectStart = 0;
    $(".selected").removeClass("selected");
    $("div:jqmData(role='footer')").hide();
});

$(document).on("click", "th", function (event) {
    var id = $(event.target).text();
    orderTable(id);
});

$(document).on("click", "td .ui-table-cell-label", function (event) {
    $.mobile.loading('show');
    $(this).css({
        "background": "url(styles/images/icons-svg/carat-r-black.svg) no-repeat 6px center"
    });
    setTimeout(function() {
        orderTable($(event.target).text());
    }, 200);
});

$(window).on("orientationchange", function (event) {
    promoteSelectedColumn(event.orientation);
});

$(document).one("pagebeforeshow", "#page-1", function () {
    $(window).orientationchange();
});

$(document).on("click", "tbody tr", function() {
    $(".selected").not(this).removeClass("selected");

    if (!shifted) {
        selectStart = $(this).index();
        $(this).toggleClass("selected");
    } else {
        var start = selectStart;
        var end = $(this).index();
        if (end < start) {
            start = $(this).index();
            end = selectStart;
        }

        selectStart = start;
        var numSelected = end - start;

        $(this).parent().find('tr').slice(selectStart).each(function(index) {
            $(this).addClass("selected");
            if (index == numSelected) {
                return false;
            }
        });
    }

    if ($(".selected").length == 1) {
        $("#edit-btn").show();
    } else {
        $("#edit-btn").hide();
    }

    var footer = $("div:jqmData(role='footer')");
    if (($(this).hasClass("selected") && !footer.is(":visible")) ||
        (!$(this).hasClass("selected") && footer.is(":visible"))) {
        footer.slideToggle("fast");
    }
});

function orderTable(id) {
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

    $('body').jtable("refresh", {
        orderBy: orderBy.val(),
        asc: orderDir.val() === "asc",
        elementsPerPage: $("body").data("elements-per-page")
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
    $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "none"});

    contentHeight();
}

var swapIndex = -1;
var otherIndex = -1;

function contentHeight() {
    var screen = $.mobile.getScreenHeight();
    var header = $(".ui-header").hasClass("ui-header-fixed") ? $(".ui-header").outerHeight()  - 1 : $(".ui-header").outerHeight();
    var footer = $(".ui-footer").hasClass("ui-footer-fixed") ? $(".ui-footer").outerHeight() - 1 : $(".ui-footer").outerHeight();
    var contentCurrent = $(".ui-content").outerHeight() - $(".ui-content").height(),
        content = screen - header - footer - contentCurrent - 1000;
    $(".ui-page").css("min-height", content);
}

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