$(document).one('pagecreate', function() {
    $(":mobile-pagecontainer").pagecontainer({
        change: function() { hashChanged(); }
    });
});

$(document).on('change', '#page-select', function () {
    location.hash = $(this).find('option:selected').val();
});

$(document).on('click', '#nav-btn-next', function () {
    var next = $($('#page-select option:selected').val()).jqmData("next");
    navnext(next);
});

$(document).on('click', '#nav-btn-prev', function () {
    var prev = $($('#page-select').find('option:selected').val()).jqmData("prev");
    navprev(prev);
});

$(document).on("swipeleft", ".ui-page", function() {
    var next = $(this).jqmData("next");
    navnext(next);
});

$(document).on("swiperight", ".ui-page", function() {
    var prev = $(this).jqmData("prev");
    navprev(prev);
});

function navnext(next) {
    if (next) {
        $(":mobile-pagecontainer").pagecontainer("change", next, {
            transition: "slide"
        });

        $(".ui-table-cell-label").parent().contents().filter(function () {
            return this.nodeType == 3;
        }).wrap("<b class=\"ui-table-cell-title\"></b>");
    }
}

function navprev(prev) {
    if (prev) {
        $(":mobile-pagecontainer").pagecontainer("change", prev, {
            transition: "slide",
            reverse: true
        });
    }
}

function hashChanged() {
    var hash = "#page-1";
    if (location.hash) {
        hash = location.hash;
    }

    $('#page-select').find('option[value=' + hash + ']').prop('selected', 'selected');
    $('#page-select').selectmenu("refresh", true);
}