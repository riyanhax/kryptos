$.widget('formBuilder.fbMultiSelect', {
    options: {
        hiddenInput: null
    },

    _create: function () {
        if (!this.options.hiddenInput) {
            throw new Error('hiddenInput parameter is not assigned');
        }
        let $hiddenInput = $(this.options.hiddenInput);
        $hiddenInput.val($hiddenInput.val().replace(' ', ','));
        $hiddenInput.parents('.form-group:first').hide();
        this._initWidgetValue(this.element, $hiddenInput.val().split(','));
        this._applyWidget(this.element);
        this._applyChangeEvent(this.element, this.options.hiddenInput);
    },

    _applyWidget: function (input) {
        let $select = $(input);
        $select.find('option[label=Select]')
            .remove();
        $select.removeClass('form-control')
            .selectpicker();
        $select.parents('.input-wrap:first')
            .find('.bootstrap-select')
            .css({'width': '100%', 'margin': '0'});
    },

    _initWidgetValue: function (input, values) {
        let $select = $(input).attr({
            multiple: true,
            name: null
        });
        $(values).map(function (i, value) {
            $select.find('[value="'+value+'"]').attr('selected', true);
        });
    },

    _applyChangeEvent: function (input, hiddenInput) {
        let $select = $(input);
        $select.on('change', function () {
            $(hiddenInput).val($select.val().join(','));
        });
    }
});
