var cgcci;!function(c){cgcci={new_button:"",init:function(){c(".cgcci-quiz-button").on("click",function(){var i=c(this);return i.hasClass("please-wait")?!1:void 0});var i=c(".quiz-timer");i.length&&i.data("in-seconds")<=0&&cgcci.end_quiz(),cgcci.start_timers()},disable_button:function(c){c.html("Quiz in Progress").addClass("taking-quiz"),c.off("click").on("click",function(){return!1})},build_target:function(){var i=c("<div />").addClass("cgcci-quiz"),t=c("<a />").attr("href","#close-quiz");return t.on("click",function(){return confirm("Are you sure you want to quit this quiz?")&&c(this).parents(".cgcci-quiz").slideUp("fast"),!1}),i.append(t),i.append(c("<div />").addClass("cgcci-quiz-content")),i},start_timers:function(){c.each(c(".countdown-timer"),function(i,t){var n=c(t),a=n.data("format")?n.data("format"):"MS",e=n.data("in-seconds")?n.data("in-seconds"):new Date,u=(n.data("expiry-callback")?n.data("expiry-callback"):"",{until:e,compact:!0,format:a,onTick:cgcci.tick_callback});n.hasClass("quiz-timer")?u.onExpiry=cgcci.end_quiz:n.hasClass("wait-timer")&&(u.onExpiry=cgcci.enable_quiz),n.countdown(u)})},enable_quiz:function(){cgcci.get_new_button()&&c("cgcci-quiz-button.please-wait").replaceWith(cgcci.get_new_button())},end_quiz:function(){var i=c(".cgcci-quiz-form:first");if(i.length){var t=c("<div />").addClass("cgcci-quiz-ended").html("The quiz has ended.");i.before(t),t.slideDown("fast"),i.slideUp("fast"),i.submit()}},tick_callback:function(i){var t=c.countdown.periodsToSeconds(i);c('input[type="hidden"][name="cgcci_time_remaining"]').val(t);var n=c(".countdown-timer");if(5==t){n.addClass("ending-soon");var a=n.parents(".please-wait");a.length&&cgcci.setup_button()}else 1>t&&n.removeClass("ending-soon").addClass("ended")},get_new_button:function(){return cgcci.new_button?cgcci.new_button:c(c.ajax({type:"POST",async:!1,url:cgc_scripts.ajaxurl,data:{quiz_id:quiz_id,action:"cgcci_get_button"}}).responseText)},setup_button:function(){c.ajax({type:"POST",async:!1,url:cgc_scripts.ajaxurl,data:{quiz_id:quiz_id,action:"cgcci_get_button"},success:function(i){cgcci.new_button=c(i)}})}},c(function(){cgcci.init()})}(jQuery);