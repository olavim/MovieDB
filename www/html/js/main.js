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
            var selected = $(".selected");
            var picked = selected.eq(0).is(".picked") ? 0 : 1;

            if (picked) {
                selected.addClass("picked");
            } else {
                selected.removeClass("picked");
            }

            var ids = [];
            selected.each(function() {
                ids.push($(this).jqmData("id"));
            });
            ids = ids.join(';');

            jsonAjaxCall("api/entries/"+ids, 'PUT', {picked: picked});
        },
        deleteConfirmBtnClick = function() {
            var ids = [];
            var selected = $(".selected");
            selected.each(function() {
                ids.push($(this).jqmData("id"));
            });

            jsonAjaxCall("api/entries/"+ids.join(';'), 'DELETE', function(data, textStatus, request) {
                if (request.status == 204) {
                    selected.remove();

                    var json = $('body').data('json');
                    $('body').data('json', $.grep(json, function (element, index) {
                        return $.inArray(element.id, ids);
                    }));
                }
            });
        },
        editBtnClick = function() {
            toggleEditEntry($(".selected").eq(0));
        },
        editCancelBtnClick = function() {
            toggleEditEntry($(".selected").eq(0));
            $("#modify-footer").slideToggle("fast");
        },
        addBtnClick = function() {
            jsonAjaxCall("api/entries", "POST", function(data) {
                var id = data.entryId;
                var table = $(":mobile-pagecontainer").pagecontainer('getActivePage').find(':jqmData(role="table")');
                var tbody = table.find('tbody');
                var tr = $('<tr data-id="'+id+'"></tr>');
                var columns = {};
                table.find('th').each(function() {
                    var td = $('<td></td>');
                    td.append($('<b class="ui-table-cell-label">'+$(this).html()+'</b>'));
                    td.append($('<b class="ui-table-cell-title">&nbsp;</b>'));
                    tr.append(td);
                    columns[$(this).html()] = '';
                });
                tbody.prepend(tr);

                $(".selected").removeClass('selected');
                tr.addClass('selected');
                toggleEditEntry($(".selected").eq(0));

                $('body').data('json').push({id: id, picked: '', data: columns});
            });
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
            $("#edit-cancel-btn").click(editCancelBtnClick);
            $("#add-btn").click(addBtnClick);
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
    elem.find('.ui-table-cell-editfield').each(function() {
        var heading = $(this).siblings('.ui-table-cell-label').eq(0).text();
        var value = $(this).val();
        object[heading] = value;
    });

    var id = elem.jqmData("id");
    var columns = {};
    jsonAjaxCall("api/entries/"+id, 'PUT', object, function () {
        elem.find('.ui-table-cell-editfield').each(function () {
            if (!$(this).siblings('.ui-table-cell-title').length) {
                var title = $('<b class="ui-table-cell-title"></b>');
                title.appendTo($(this).parent());
            }

            var column = $(this).siblings('.ui-table-cell-label').html();
            var value = $(this).val() ? $(this).val() : '&nbsp;';
            $(this).siblings('.ui-table-cell-title').html(value);

            columns[column] = value;
        });

        toggleEditEntry(elem);
        $("#modify-footer").slideToggle("fast");

        for (var i = 0; i < $('body').data('json').length; i++) {
            if ($('body').data('json')[i].id == id) {
                $('body').data('json')[i].data = columns;
                break;
            }
        }
    });
});

function refreshNavigation() {
    var view = $('<ul data-role="listview"></ul>');
    var index = $('.ui-highlight').index();

    jsonData = $('body').jqmData('json');
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
    var data = (typeof params == 'object') ? params : {};
    $.ajax({
        url: url,
        type: type,
        contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify(data),
        beforeSend: function () {
            showLoader();
        },
        success: function (data, textStatus, request) {
            console.log(request);
            if (request.status < 300) {
                hideLoader();
                if ($.isFunction(params)) {
                    successCallback = params;
                    params = undefined;
                }

                if ($.isFunction(successCallback)) {
                    successCallback.call(this, data, textStatus, request);
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