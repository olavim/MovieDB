(function ($) {

    $.fn.toggleClick = function (data) {

        var index = 0;
        var funcs = Array.prototype.slice.call(arguments, 1);
        var numFuncs = funcs.length;

        if($.isFunction(data) || data === false) {
            funcs = [data].concat(funcs);
            numFuncs += 1;
            data = undefined;
        }

        // remove existing bindings
        this.unbind("click");

        this.bind("click", data, function(event) {
            funcs[index].call(this, event);
            index = (index + 1) % numFuncs;
        });

        return this;

    }

}(jQuery));