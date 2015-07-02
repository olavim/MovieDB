$(document).one('pagecreate', function() {
    $(":mobile-pagecontainer").pagecontainer({
        change: function() { hashChanged(); }
    });
});

$(document).on('change', '#nav-select', function () {
    location.hash = $(this).find('option:selected').val();
});

$(document).on("swipeleft", ".ui-page", function() {
    navnext($(this).jqmData("next"));
});

$(document).on("swiperight", ".ui-page", function() {
    navprev($(this).jqmData("prev"));
});

if (isMobile()) {
    $(document).on('pageshow', '.ui-page:not(#nav-page)', function () {
        $("#nav-button").toggleAction("click", {clear:true}, function() {
            var next = "#" + $(":mobile-pagecontainer").pagecontainer("getActivePage").attr("id");
            $("#nav-page").attr("data-next", next);
            $("#nav-page").jqmData("next", next);
            navprev($("#nav-page"));
        }, function() {
            navnext($("#nav-page").jqmData("next"));
        });
    });
}

function isMobile() {
    try{
        document.createEvent("TouchEvent");
        return true;
    } catch(e){
        return false;
    }
}

function navnext(next) {
    if (next) {
        $(":mobile-pagecontainer").pagecontainer("change", next, {
            transition: "slide"
        });
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

    if (hash !== "#nav-page") {
        $('#page-number').text("Page " + hash.substr(6));
    }
}