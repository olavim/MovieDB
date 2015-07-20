var jsonData;

var MAIN = MAIN || (function() {
    var resize = function(event) {
            var activePage = $(':mobile-pagecontainer').pagecontainer('getActivePage');
            var content_height = activePage.children('[data-role="content"]').height(),
                header_height = activePage.children('[data-role="header"]').height(),
                footer_height = activePage.children('[data-role="footer"]').height(),
                window_height = $(this).height();

            if (content_height < (window_height - header_height - footer_height)) {
                activePage.css('min-height', (content_height + header_height + footer_height));
                setTimeout(function () {
                    activePage.children('[data-role="footer"]').css('top', 0);
                }, 500);
            }
            event.stopImmediatePropagation();
        },
        pickBtnClick = function() {
            $(".selected").toggleClass("picked");
            $(".selected").each(function() {
                $.ajax({
                    url: "set_picked.php",
                    type: "get",
                    contentType: 'application/json',
                    dataType: "json",
                    data: {
                        id: $(this).jqmData("id"),
                        state: $(this).is(".picked") ? "on" : "off"
                    },
                    success: function(data) {
                        if (data.status == 'error') {
                            if (window.console && window.console.log) {
                                console.log(data);
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        if (window.console && window.console.log) {
                            console.log(xhr);
                            console.log(status);
                            console.log(error);
                        }
                    }
                });
            });
        },
        deleteConfirmBtnClick = function() {
            var id = '',
                numSelected = $(".selected").length;

            $(".selected").each(function(index) {
                id += $(this).jqmData("id");
                if (index < numSelected - 1) {
                    id += ',';
                }
            });

            window.location.href = 'delete_entry.php?id='+id;
        },
        editBtnClick = function() {
            toggleEditEntry($(".selected").eq(0));
        },
        documentClick = function(event) {
            var target = $(event.target);
            if (target.parents('.ui-page').length) {
                if (!target.hasClass('edit-on') && !target.parents('.edit-on').length && $(document).find('.edit-on').length) {
                    toggleEditEntry($('.edit-on').eq(0));
                }
            }
        },
        viewselectChange = function() {
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
                dataType: "json",
                data: {
                    elementsPerPage: $("body").data("elements-per-page")
                },
                success: function(data) {
                    if (data.status == 'error') {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    if (window.console && window.console.log) {
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                }
            });
        };

    return {
        init: function() {
            $("[data-role='header'], [data-role='footer']").toolbar({theme: "a"});
            $("div:jqmData(role='footer')").hide();
            $("[data-role='popup']").enhanceWithin().popup();
            $("body>[data-role='panel']").panel();

            $(window).bind('resize', function(event) { resize(event) }).trigger('resize');

            $("#pick-btn").click(pickBtnClick);
            $("#delete-confirm-btn").click(deleteConfirmBtnClick);
            $("#edit-btn").click(editBtnClick);
            $(document).click(documentClick(event));
            $(':jqmData(role="viewselect")').change(viewselectChange);
        }
    };
}());

$(document).on('pagebeforeshow', '.ui-page', function() {
    $(this).find(':jqmData(role="viewselect")').selectmenu("refresh");
});

$(document).on("click", "#edit-save-btn", function() {
    var elem = $('.edit-on').eq(0);
    var object = {};
    object['id'] = elem.jqmData("id");
    elem.find('.ui-table-cell-editfield').each(function() {
        var heading = $(this).siblings('.ui-table-cell-label').eq(0).text();
        var value = $(this).val();
        object[heading] = value;
    });

    jsonAjaxCall("edit_entry.php", "post", {
        form: object
    }, function (data) {
        console.log(data);
        elem.find('.ui-table-cell-editfield').each(function () {
            if (!$(this).siblings('.ui-table-cell-title').length) {
                var title = $('<b class="ui-table-cell-title"></b>');
                title.appendTo($(this).parent());
            }

            var value = $(this).val();
            $(this).siblings('.ui-table-cell-title').html(value);
        });
        toggleEditEntry(elem);
        $("#modify-footer").slideToggle("fast");
    });
});

function refreshNavigation() {
    var view = $('<ul data-role="listview"></ul>');
    var index = $('.ui-highlight').index();
    for (var i = 1; i <= Math.ceil(jsonData.length / $("body").data("elements-per-page")); i++) {
        var id = "#page-" + i;
        var text = $(id).find('td').eq(index).html().substr(0, 5);
        view.append($('<li><a href="'+id+'" data-transition="slide">Page ' + i + ' - ' + text + '</a></li>'));
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

function jsonAjaxCall(url, type, params, successCallback) {
    $.ajax({
        url: url,
        type: type,
        dataType: 'json',
        data: params,
        beforeSend: function () {
            showLoader();
        },
        success: function (data) {
            if (data.status == 'success') {
                hideLoader();
                if ($.isFunction(successCallback)) {
                    successCallback.call(this, data);
                }
            } else if (window.console && window.console.log) {
                console.log(data);
            }
        },
        error: function (xhr, status, error) {
            if (window.console && window.console.log) {
                console.log(xhr);
                console.log(status);
                console.log(error);
            }
        }
    });
}