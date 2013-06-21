/*
 * Newsletter
 * @author Gennadiy Ukhanov
 * @version 0.0.1
 * @build 1 (06/06/2011 11:18 AM)
 */
(function(_global, $){

    /**
     * @namespace Newsletter
     * @name Newsletter
     */
    bfm.utils.Newsletter = {

        /**
         * @public
         * @param form jquery dom link
         */
        create : function(_form) {
            this.form = $(_form);
            this.form.submit($.proxy(this, '_onFormSubmit'));
        },

        /**
         * Services method for disable form
         * @private
         */
        _disableForm : function() {
            this.form.find('input[type=text]').attr('disabled', 'disabled');
            this.form.find('input[type=submit]').attr('disabled', 'disabled');
            this.form.addClass('disabled');
        },

        /**
         * Services method for enable form
         * @private
         */
        _enableForm : function() {
            this.form.find('input[type=text]').removeAttr('disabled');
            this.form.find('input[type=submit]').removeAttr('disabled');
            this.form.removeClass('disabled');
        },

        /**
         * Get object with all services vars
         * @private
         * @return {object}
         */
        _getDataFromForm : function() {

            var items = $('._for_sending');
            var obj = {};

            for(var i=0; i<items.length; i++) {
                var key = $(items[i]).attr('value');
                var id = $(items[i]).attr('name');
                if($(items[i]).hasClass('type-checkbox')) {
                    key = $(items[i]).is(":checked");
                    if(!key) {
                        key = null;
                        id = null;
                    }
                }
                if(id!=null) {obj[id] = key;}
            }

            return obj;
        },

        /**
         * Ajax request from form
         * @private
         */
        _sendForm : function() {
			$.ajax({
                url: this.form.attr('action'),
                type: this.form.attr('method'),
                data: this._getDataFromForm(),
                dataType: 'json',
                success: $.proxy(this, '_onFormSendedSuccess'),
                error: $.proxy(this, '_onFormSendedError')
            });
        },

        /**
         * On form submit event
         * @private
         */
        _onFormSubmit : function() {

            if(this._messageBox!= null) {
                this._messageBox.remove();
            }
            var emailInput = this.form.find('.email');
            var email = emailInput.val();

            if(emailInput.length!=0) {

            /* regexp for checking email */
            if((/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/).test(email)) {
                this._disableForm();
                this._sendForm();
            } else {
                this.buildMessageBox(bfm.messages.email_validate, true);
            }
            } else {
                this._disableForm();
                this._sendForm();
            }

            return false;
        },

        /**
         * Event if form sended result success
         * @private
         */
        _onFormSendedSuccess : function(data) {
            this._enableForm();
			$('.popup-fixed').show();
			$('.popup-box').show();
//			this.buildMessageBox(data['result'], false);
        },


        /**
         * Event if form sended result error
         * @private
         */
        _onFormSendedError : function() {

            this._enableForm();
            this.buildMessageBox(bfm.messages.error_json_loaded, true);
        },

        /**
         * Build output message
         * @public
         */
        buildMessageBox : function(data, error) {
            error = error || false;

            this._messageBox = $(
                "<div class='message-box'>"+
                    "<div class='message-box-inner'>"+
						"<h1" + (error ? ' class="hide"' : '') + ">MERCI!</h1>"+
                        "<p" + (error ? ' class="error"' : ' class="success"') + ">"+ data +"</p>"+
                        "<a class='message-box-close' href='#/close/'>Close box</a>"+
                    "</div>"+
                "</div"
            );
            this._messageBox.appendTo(this.form).hide().fadeIn();
            this._messageBox.find('.message-box-close').click($.proxy(this, 'messageBoxClose'));
        },

        /**
         * @private
         */
        messageBoxClose : function() {
            this._messageBox.fadeOut($.proxy(this, '_onMessageBoxClose'));
            return false;
        },

        /**
         * @private
         */
        _onMessageBoxClose : function() {
            this._messageBox.remove();
        }

    }
    
    /**
     * Constructor
     */
    _global = bfm.utils.Newsletter;
    /**
     * Newsletter containers
     * @public
     */
    _global.form = null;
    _global.url = null;

    /**
     * Newsletter containers
     * @private
     */
    _global._submit = null;
    _global._input = null;
    _global._messageBox = null;

})(window || this, jQuery);