(function(){
    let formGroups = $('.content-page form:visible .form-group');

    /**
     * @param {object} input
     * @param {string} parameter
     * @returns {object}
     */
    function parseParameter(input, parameter) {
        let attr = $(input).data(parameter);
        if (typeof attr === 'string') {
            try {
                attr = JSON.parse(attr);
            } catch (e) {
                attr = {expression: 'true'};
            }
        }
        return attr;
    }

    /**
     * @param {string} expression
     * @param {object} bindings
     * @returns {boolean}
     */
    function processExpression(expression, bindings) {
        let newBindings = {}, bindingName, bindingValue, bindingField;
        for (bindingName in bindings) {
            if(!bindings.hasOwnProperty(bindingName)) {
                continue;
            }
            bindingValue = bindings[bindingName];
            if (bindingField = getBindedField(bindingValue)) {
                bindingValue = getFieldValue(bindingField);
            }
            newBindings[bindingName] = bindingValue;
        }
        if (expression === 'a == b') {
            // TODO: resolve problem with string comparison
            return newBindings.a === newBindings.b;
        }
        return math.eval(expression, newBindings);
    }

    /**
     * @param {object} fieldGroup
     * @returns {object|null}
     */
    function getFieldByGroup(fieldGroup) {
        let fields = $(fieldGroup).find(':input:not(button)');
        if (fields.length) {
            return fields;
        }
        return null;
    }

    /**
     * @param {string} binding
     * @returns {object|null}
     */
    function getBindedField(binding) {
        let fieldGroup;
        if (binding.substr(0,1)==='{') {
            fieldGroup = formGroups.filter('.' + binding.substr(1, binding.length-2));
            if (fieldGroup) {
                return getFieldByGroup(fieldGroup);
            }
        }
        return null;
    }

    /**
     * @param {object} input
     * @returns {string|null}
     */
    function getFieldValue(input) {
        let result;
        switch ($(input).attr('type')) {
            case 'checkbox':
            case 'radio':
                result = $(input).filter(':checked').val();
                break;
            default:
                result = $(input).val();
                break;
        }
        return result + '';
    }

    /**
     * @param {object} input
     * @param {function} callback
     */
    function initFieldChangeEvent(input, callback) {
        if ( $(input).attr('type') === 'radio' )  {
            $(input).on('ifChanged', callback);
            return;
        }
        $(input).on('input change', callback);
    }

    /**
     * @param {object} input
     * @param {function} callback
     * @param {object} data
     */
    function initField(input, callback, data) {
        let field;
        if ($.isPlainObject(data.bindings)) {
            for (let bindingName in data.bindings) {
                if(!data.bindings.hasOwnProperty(bindingName)) {
                    continue;
                }
                if (field = getBindedField(data.bindings[bindingName])){
                    initFieldChangeEvent(field, function(){
                        callback(processExpression(data.expression, data.bindings));
                    });
                }
            }
        }
        callback(processExpression(data.expression, data.bindings));
    }

    formGroups.filter('[data-visible-if]').each(function(i, inputGroup){
        initField(inputGroup, function (result) {
            if (result) {
                $(inputGroup).show();
                return;
            }
            $(inputGroup).hide();
        }, parseParameter(inputGroup, 'visible-if'));
    });

    formGroups.filter('[data-enable-if]').each(function(i, inputGroup){
        initField(inputGroup, function (result) {
            let input = getFieldByGroup(inputGroup);
            if (!input) {
                return;
            }
            $(input).attr('disabled', !result);
        }, parseParameter(inputGroup, 'enable-if'));
    });
})();
