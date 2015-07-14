var jsonData;

$(function () {
    $("[data-role='header'], [data-role='footer']").toolbar({theme: "a"});
    $("div:jqmData(role='footer')").hide();
    $("[data-role='popup']").enhanceWithin().popup();
    $("body>[data-role='panel']").panel();

    $(window).bind('resize', function (event) {
        var content_height = $.mobile.activePage.children('[data-role="content"]').height(),
            header_height  = $.mobile.activePage.children('[data-role="header"]').height(),
            footer_height  = $.mobile.activePage.children('[data-role="footer"]').height(),
            window_height  = $(this).height();

        if (content_height < (window_height - header_height - footer_height)) {
            $.mobile.activePage.css('min-height', (content_height + header_height + footer_height));
            setTimeout(function () {
                $.mobile.activePage.children('[data-role="footer"]').css('top', 0);
            }, 500);
        }
        event.stopImmediatePropagation();
    }).trigger('resize');
});

$(document).on("click", "#pick-btn", function() {
    $(".selected").toggleClass("picked");
    $(".selected").each(function() {
        $.ajax({
            url: "set_picked.php",
            type: "get",
            data: {
                id: $(this).jqmData("id"),
                state: $(this).is(".picked") ? "on" : "off"
            }
        });
    });
});

$(document).on("click", "#delete-btn-confirm", function() {
    var id = "";
    var numSelected = $(".selected").length;

    $(".selected").each(function(index) {
        id += $(this).jqmData("id");
        if (index < numSelected - 1) {
            id += ",";
        }
    });

    window.location.href = 'delete_entry.php?id='+id;
});

$(document).on("click", "#edit-btn", function() {
    toggleEditEntry($(".selected").eq(0));
});

$(document).on("click", function(event) {
    var target = $(event.target);
    if (target.parents('.ui-page').length) {
        if (!target.hasClass('edit-on') && !target.parents('.edit-on').length && $(document).find('.edit-on').length) {
            toggleEditEntry($('.edit-on').eq(0));
        }
    }
});

$(document).on('change', ':jqmData(role="viewselect")', function () {
    $("body").data("elements-per-page", $(this).val());
    $("body").jtable("refresh", {
        elementsPerPage: $("body").data("elements-per-page")
    });
    $(":mobile-pagecontainer").pagecontainer("change", "#page-1", {transition: "none"});
    refreshNavigation();
    $(window).resize();

    $.ajax({
        url: "session.php",
        type: "get",
        data: {
            elementsPerPage: $("body").data("elements-per-page")
        }
    });
});

$(document).on('pagebeforeshow', '.ui-page', function() {
    $(this).find(':jqmData(role="viewselect")').selectmenu("refresh");
});

$(document).on("click", "#edit-btn-save", function() {
    var elem = $('.edit-on').eq(0);
    var form = new Array();
    var object = {};
    object['id'] = elem.jqmData("id");
    elem.find('.ui-table-cell-editfield').each(function() {
        var heading = $(this).siblings('.ui-table-cell-label').eq(0).text();
        var value = $(this).val();
        object[heading] = value;
    });
    form.push(object);

    $.ajax({
        url: "edit_entry.php",
        type: "post",
        data: {
            form: form
        },
        success: function() {
            elem.find('.ui-table-cell-editfield').each(function() {
                var value = $(this).val();
                $(this).siblings('.ui-table-cell-title').html(value);
            });
            toggleEditEntry(elem);
            $("#modify-footer").slideToggle("fast");
        }
    });
});

function refreshNavigation() {
    var view = $('<ul data-role="listview"></ul>');
    for (var i = 1; i <= Math.ceil(jsonData.length / $("body").data("elements-per-page")); i++) {
        view.append($('<li><a href="#page-' + i + '" data-transition="slide">Page ' + i + '</a></li>'));
    }

    if (!isMobile()) {
        $("#nav-panel").html(view);
    } else {
        $("#nav-page").html(view);
    }

    view.listview();
}

function showLoader() {
    $("body").append(
        $("<div id=\"loader\"></div>").css({
            "position": "absolute",
            "z-index": "99999",
            "display": "table",
            "height": "100%",
            "width": "100%"
        }).append(
            $("<div>Loading...</div>").css({
                "display": "table-cell",
                "vertical-align": "middle",
                "text-align": "center",
            })
        )
    );
}

function hideLoader() {
    $("#loader").fadeOut(100, function() {
        $(this).remove();
    });
}