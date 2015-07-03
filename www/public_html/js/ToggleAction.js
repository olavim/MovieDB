(function ($) {

    $.fn.toggleAction = function (type, data) {
        var functions = Array.prototype.slice.call(arguments, 2);
        return this.each(function() {
            new $.ToggleAction(this, type, data, functions);
        });
    }

    $.ToggleAction = function(element, type, data, functions) {
        this.element = (element instanceof $) ? element : $(element);
        this.type = type;
        this.data = data;
        this.functions = functions;
        this.functionIndex = 0;
        this.numFunctions = this.functions.length;

        if (data instanceof Object) {
            this.settings = $.extend(this.settings, data);
        }

        this.init();
    }

    $.ToggleAction.prototype.init = function () {
        if($.isFunction(this.data) || this.data === false) {

            // if data is a function, push it to the beginning of the function array
            this.functionUnshift(this.data);

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

            _this.rotateFunctionIndex();
        });
    }

    $.ToggleAction.prototype.rotateFunctionIndex = function () {
        this.functionIndex = (this.functionIndex + 1) % this.numFunctions;
    }

    $.ToggleAction.prototype.functionUnshift = function (obj) {
        this.functions = [obj].concat(this.functions);
        this.numFunctions++;
    }

    $.ToggleAction.settings = {
        clear: false
    }

}(jQuery));