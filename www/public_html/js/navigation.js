$(document).one('pagecreate', function() {
    $(":mobile-pagecontainer").pagecontainer({
        change: function() { hashChanged(); }
    });
});

$(document).on('change', '#nav-select', function () {
    location.hash = $(this).find('option:selected').val();
});

$(document).on('pageinit', '.ui-page', function() {
    $(this).on("swipeleft", function(event) {
        if (!$(event.target).is('input')) {
            var next = $(':mobile-pagecontainer').pagecontainer('getActivePage').jqmData("next");
            navnext(next);
        }
        event.stopImmediatePropagation();
    });

    $(this).on("swiperight", function(event) {
        if (!$(event.target).is('input')) {
            var prev = $(':mobile-pagecontainer').pagecontainer('getActivePage').jqmData("prev");
            navprev(prev);
        }
        event.stopImmediatePropagation();
    });
})

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
    $('#page-number').text($(":mobile-pagecontainer").pagecontainer("getActivePage").jqmData("name"));
}