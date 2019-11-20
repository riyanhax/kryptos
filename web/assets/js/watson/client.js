$.fn.watson = function(options) {
    var container = $(this);
    var form = $(this).find('form');
    var closeButton = $(this).find('.watson-close');
    var openButton = $('.watson-open');
    var messageBox = $(this).find('.watson-message-box');
    var loader = '<div class="message-loader"></div>';
    var initPhrase = $(this).attr('data-init-message');
    
    form.on("submit", function(event) {
        event.preventDefault();
        var input = $(this).find('.watson-input');
        var message = input.val();
        
        input.val('');
        sendMessage(message, {appendRequest: true});
    });
    
    closeButton.on("click", function(event) {
        event.preventDefault();
        closeBox();
    });
    
    openButton.on("click", function(event) {
        event.preventDefault();
        openBox();
    });
    
    function sendMessage(message, options) {
        if (message.length > 0) {
            if (options.appendRequest === true) {
                appendMessage(message, 'request');
            }
            
            var responseBox = appendMessage(loader, 'response');
            var context = container.attr('data-context');
            
            if (context.length > 0) {
                context = $.parseJSON(context);
            }
            
            $.ajax({
                url: "/ajax/watson",
                type: "POST",
                data: {message: message, init_phrase: initPhrase, context: context},
                success: function(response) {
                    try {
                        var data = $.parseJSON(response);
                        
                        if (data.is_error === false) {
                            responseBox.html(data.message);
                            container.attr('data-context', JSON.stringify(data.context));
                        }
                    } catch (e) {
                        console.log(e);
                    }
                }
            });
        }
    }
    
    function appendMessage(message, type) {
        var box = $('<div/>');
        box.attr('class', 'chat-message');
        box.attr('data-type', type);
        box.html(message);
        
        var clear = $('<div/>');
        clear.attr('class', 'clear');
        
        messageBox.append(box);
        messageBox.append(clear);
        
        messageBox.stop().animate({ scrollTop: messageBox[0].scrollHeight}, 1000);
        
        return box;
    }
    
    function closeBox() {
        container.slideToggle();
        container.attr('data-folded', '1');
    }
    
    function openBox() {
        container.slideToggle();
        container.attr('data-folded', '0');
        container.css('display', 'flex');
        
        if (container.attr('data-initialized') !== '1') {
            sendMessage(initPhrase, {appendRequest: false});
            container.attr('data-initialized', '1');
        }
    }
};