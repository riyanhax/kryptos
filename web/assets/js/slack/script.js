	$(document).ready(function () {

/* $.ajax({
						url: "https://slack.com/api/channels.create"
						,type: "POST"
						,dataType: "json"
						,data: {
							token: $('#apiToken').val(),
							name:$('#user').val(),
							validate:true,
						}
						,success: function (response) {
							if(response.ok) {
								
							//	alert(response.channel.id);
								
								$('#channelId').val(response.channel.id);
							}
							
							
						}
}); */
						


				/* $('.message-box > a').slackChat('destroy');
				$('.message-box').hide(); */

				$('[data-toggle=tooltip]').tooltip();

				//$('.btn-try').on('click', function (e) {
				$('.message-box').on('click', function (e) {
				
				
					e.preventDefault();
					e.stopPropagation();

					$('.message-box > a').slackChat('destroy');
					$('.message-box').hide();
					slackChatOptions = {
						apiToken: $('#apiToken').val()
						,channelId: $('#channelId').val()
						,user: $('#user').val()
						,userLink: 'http://www.google.com'
						,userId: 'TEST-USER'
						,userImg: '/assets/img/users/user-35.jpg'
						,defaultSysImg: '/assets/img/users/user-35.jpg'
						,defaultSysUser: $('#sysUser').val()
						,botUser: $('#botUser').val()
						,elementToDisable: $('.message-box')
						,disableIfAway: false
						,webCache: true
						,debug:false
						,privateChannel: false
						,badgeElement: '.slack-message-count'
						,useUserDetails: false
						,defaultInvitedUsers: []
						
					};
					
					if($('#usePrivateChannel').prop('checked')) {
						slackChatOptions.privateChannel = true;
						slackChatOptions.serverApiGateway = $('#serverApiGateway').val();
						slackChatOptions.defaultInvitedUsers = $('#defaultInvitedUsers').val().split(",");
					}

				$('.message-box > a').slackChat(slackChatOptions);

				$('.message-box').show();
					
/*  $(".chat-container .watson").slideToggle();
$(".chat-input").remove();
$(".watson-message-box").remove();
$(".chat-header").after('<div class="slackchat slack-chat-box"><div class="slack-message-box"></div><div class="send-area"><textarea class="form-control slack-new-message" type="text" placeholder="Zespół Kryptos72, pozostaje do Twojej dyspozycji..."></textarea><div class="slack-post-message"><i class="fa fa-fw fa-chevron-right"></i></div></div></div>');  */

					return false;

				});

				//load from localStorage
				for ( var i = 0, len = localStorage.length; i < len; ++i ) {

					if(localStorage.key(i) == 'scParams') {
						var scParams = JSON.parse(localStorage.getItem( localStorage.key( i ) ));

						if(scParams.apiToken) $('#apiToken').val(scParams.apiToken);
						if(scParams.channelId) $('#channelId').val(scParams.channelId);
						if(scParams.user) $('#user').val(scParams.user);
						if(scParams.defaultSysUser) $('#sysUser').val(scParams.defaultSysUser);
						if(scParams.botUser) $('#botUser').val(scParams.botUser);
						if(scParams.serverApiGateway) $('#serverApiGateway').val(scParams.serverApiGateway);
						if(scParams.defaultInvitedUsers) $('#defaultInvitedUsers').val(scParams.defaultInvitedUsers);
					}
				}

				//load the channels
				$('#listChannels').on("click", function () {
					$('.channel-list').html('').hide();

					if($('#apiToken').val() == '') {
						$('.channel-list').html("Invalid Slack API Token").show();
						return false;
					}

					$.ajax({
						url: "https://slack.com/api/channels.list"
						,type: "POST"
						,dataType: "json"
						,data: {
							token: $('#apiToken').val()
						}
						,success: function (resp) {
							if(resp.ok) {
								if(resp.channels.length) {
									var html = "<table class='table table-condensed table-striped table-bordered'><tr><th>Channel Name</td><td>Channel ID</td>";
									for(var i=0; i< resp.channels.length;i++) {
										if(!resp.channels[i].is_archived)
											html += "<tr><td>" + resp.channels[i].name + "</td><td><span class='channel-id'>" + resp.channels[i].id + "</span><a class='btn btn-xs btn-danger use-channel pull-right'>Use</a></td>";
									}
									html += "</table>";
									$('.channel-list').html(html).show();

									$('.use-channel').off('click');
									$('.use-channel').on('click', function () {
												
										$('#channelId').val($(this).parent().find('.channel-id').text());
										$('.channel-list').hide();
									
									});
								}
								else
									$('.channel-list').html("No channels found").show();	
							}
						}
					});	
				});
				 
				 
				$('#usePrivateChannel').on('change', function () {
					if($(this).prop('checked')) {
						$("#serverApi").removeClass("hide");
						$("#invitedUsers").removeClass("hide");
					}
					else {
						$("#serverApi").addClass("hide");
						$("#invitedUsers").addClass("hide");
					}
				});
				
				$(".chat_icon").click(function(){
					if($( ".chat-header" ).next('div').hasClass( "chat-messages" )){
						
					}else{
						$(".chat-header").after('<div class="chat-messages watson-message-box"></div><div class="chat-input"><form action="" method="POST"><div class="input-group"><input type="text" name="chat_message" autocomplete="off" class="watson-input form-control" placeholder="Wpisz wiadomość..."><span class="input-group-btn"><button class="btn btn-default" type="submit"><span class="fa fa-comment"></span></button></span></div></form></div>');
					}
					
/* $(".chat-header").after('<div class="chat-messages watson-message-box"></div><div class="chat-input"><form action="" method="POST"><div class="input-group"><input type="text" name="chat_message" autocomplete="off" class="watson-input form-control" placeholder="Wpisz wiadomość..."><span class="input-group-btn"><button class="btn btn-default" type="submit"><span class="fa fa-comment"></span></button></span></div></form></div>'); */
	$('.slack-chat-header').remove();
	$('.slack-message-box').remove();
	$('.slack-chat-box').remove();

				});
				
				
			});