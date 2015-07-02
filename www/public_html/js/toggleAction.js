(function ($) {

    $.fn.toggleAction = function (type, data) {
        var functions = Array.prototype.slice.call(arguments, 2);
        return this.each(function() {
            new $.ToggleClick(this, type, data, functions);
        });
    }

    $.ToggleAction = function(element, type, data, functions) {
        this.element = (element instanceof $) ? element : $(element);
        this.type = type;
        this.data = data;
        this.functions = functions;
        this.functionIndex = 0;
        this.numFunctions = this.functions.length;
        this.settings = $.extend(this.defaults, options);

        this.init();
    }

    $.ToggleAction.prototype = {

        init: function () {
            if($.isFunction(this.data) || this.data === false) {

                // if data is a function, push it to the beginning of the function array
                this.functions = [this.data].concat(this.functions);

                // thus we have one more function
                this.numFunctions++;
                this.data = undefined;
            }

            if (this.settings.clear) {

                // remove existing/previous bindings of this type
                this.element.unbind(this.type);
            }

            // cannot reference 'this' directly from inner method
            var _this = this;

            this.element.bind(this.type, this.data, function(event) {

                // call the next function on the element
                _this.functions[_this.functionIndex].call(this, event);

                // rotate the function index
                _this.functionIndex = (_this.functionIndex + 1) % _this.numFunctions;
            });
        }

    }

    $.ToggleAction.settings = {
        clear: false
    }

}(jQuery));