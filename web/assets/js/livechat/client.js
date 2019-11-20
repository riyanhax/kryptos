$(function() {
    
// LiceChat license number

const LICENSE = 10806092;
const GROUP = 0;

// init LiveChat visitor SDK

const sdk = LivechatVisitorSDK.init({
  license: LICENSE,
  group: GROUP
});

sdk.startChat();

console.log(sdk);

sdk.on('new_message', newMessage => {
  console.log(newMessage);
});

var ret = sdk.sendMessage({
    text: "Hello",
    customId: 12345
}).then(response => {
    console.log(response);
  })
  .catch(error => {
    console.log(error);
});

console.log(ret);

ret.then(function(response) {
   console.log(response); 
});

// References to DOM elements

const liveChatWindow = document.getElementById('livechat')
const offlineMessage = document.getElementById('offline-message')
const connectionMessage = document.getElementById('connection-message')
const liveChatWindowMinimized = document.getElementById('livechat-minimized')
const messageList = document.getElementById('message-list')
const sendButton = document.getElementById('send-button')
const setDataButton = document.getElementById('set-data-button')
const input = document.getElementById('message-input')
const prechatForm = document.getElementById('prechat')
const prechatEmailInput = document.getElementById('prechat_email')
const prechatNameInput = document.getElementById('prechat_name')
const minimizeButton = document.getElementById('minimize')
const queueMessage = document.getElementById('queue-message')
const queueTime = document.getElementById('queue-time')
const queueNumber = document.getElementById('queue-number')
const typingIndicator = document.getElementById('typing-indicator')
const rateGood = document.getElementById('rate-good')
const rateBad = document.getElementById('rate-bad')
const rateChat = document.getElementById('rate-chat')
const fileInput = document.getElementById('file-input')

// Agents array, 'is visitor chatting' flag

const agents = []
let chatting = false

const findAgentById = (agentId) => agents.find((agent) => agent.id === agentId)

// Append message function

const appendMessage = (text, authorType, authorId) => {
  const messageDivContainer = document.createElement('div')
  messageDivContainer.classList.add('message-container', authorType)
  if (findAgentById(authorId)) {
    const agent = findAgentById(authorId)
    const avatarImage = document.createElement('img')
    avatarImage.src = `https://${ agent.avatarUrl }`
    avatarImage.classList.add('agent-avatar')
    messageDivContainer.append(avatarImage)
  }
  const messageDiv = document.createElement('div')
  messageDiv.classList.add('message')
  messageDiv.innerHTML = '<div>' + text + '</div>'
  messageDivContainer.append(messageDiv)
  messageList.appendChild(messageDivContainer)
  messageList.scrollTop = messageList.scrollHeight
}

// show bar with 'Agent is typing' info 

const showTypingIndicator = () => {
  typingIndicator.classList.remove('hide')
}

// hide bar with 'Agent is typing' info

const hideTypingIndicator = () => {
  typingIndicator.classList.add('hide')
}


// show queue message with information about estimated waiting time and queue order number

const showQueueMessage = (time, number) => {
  queueMessage.classList.remove('hide')
  queueTime.innerHTML = time
  queueNumber.innerHTML = number
}

// hide queue message

const hideQueueMessage = () => {
  queueMessage.classList.add('hide')
}

// disable message input

const disableInput = (text) => {
  input.placeholder = text
  input.disabled = true
}

// enable message input

const enableInput = () => {
  input.placeholder = 'Write a message'
  input.disabled = false
}

// show prechat - form with questions about visitor's name and email

const showPrechat = () => {
  if (chatting) {
    return
  }
  prechatForm.classList.remove('hide')
}

// hide prechat

const hidePrechat = () => prechatForm.classList.add('hide')

const showRateChat = () => {
  rateChat.classList.remove('hide')
}

const hideRateChat = () => {
  rateChat.classList.add('hide')
}

// New message callback handler - detect author, append message

sdk.on('new_message', (data) => {
  console.log('data', data)
  const authorType = data.authorId.indexOf('@') === -1 ? 'visitor' : 'agent'
  appendMessage(data.text, authorType, data.authorId)
})

sdk.on('new_file', (data) => {
  const authorType = data.authorId.indexOf('@') === -1 ? 'visitor' : 'agent'
  appendMessage(data.url, authorType, data.authorId)
})

sdk.on('visitor_queued', (queueData) => {
  showQueueMessage(queueData.waitingTime, queueData.numberInQueue)
})


// Connection status changed callback handler - toggle message about connection problems, toggle input

sdk.on('connection_status_changed', (data) => {
  if (data.status === 'connected') {
    enableInput()  
    connectionMessage.classList.add('hide')
    if (!chatting) {
      setTimeout(showPrechat, 1000)
    }
  } else {
    disableInput('Disconnected')
    connectionMessage.classList.add('disconnected')
    connectionMessage.classList.remove('hide')
  }
})

// Chat ended callback handler, append system message and disable input

sdk.on('chat_ended', (data) => {
  appendMessage('Chat is closed', 'system')
  disableInput('Chat is closed')
  hideRateChat()
})

// Chat started callback handler - set chatting flag, hide prechat form

sdk.on('chat_started', () => {
  chatting = true
  hidePrechat()
  hideQueueMessage()
  showRateChat()
})

// Agent changed callback handler - add agent to agent's array

sdk.on('agent_changed', (data) => {
  console.log('agent data', data)
  agents.push(data)
})

// Typing indicator callback handler, show / hide bar

sdk.on('typing_indicator', (data) => {
  if (data.isTyping) {
    return showTypingIndicator()  
  }
  hideTypingIndicator()
})

// Status changed callback handler - show offline message if all agents are offline

sdk.on('status_changed', (data) => {
  if (data.status !== 'online') {
    offlineMessage.classList.remove('hide')
    disableInput('Chat is offline')
    
  } else {
    offlineMessage.classList.add('hide')
    enableInput()
  }
})

sdk.on('new_invitation', (data) => {
  console.log('New invitation', data)
})

// Use sendMessage method

const sendMessage = () => {
  const text = input.value
  sdk.sendMessage({
    customId: String(Math.random()),
    text: text,
  })
  input.value = ''
}

// Maximize / minimize chat widget

const toggleMinimized = () => {
  liveChatWindow.classList.toggle('minimized');
  liveChatWindowMinimized.classList.toggle('minimized');
}

sendButton.onclick = sendMessage

minimizeButton.onclick = toggleMinimized

liveChatWindowMinimized.onclick = toggleMinimized

input.onkeydown = (event) => {
  // send message if enter was pressed
  if (event.which === 13) {
    sendMessage()
    return false
  }
}

setDataButton.onclick = () => {
  const name = prechatNameInput.value
  const email = prechatEmailInput.value
  sdk.setVisitorData({
    name, email,
  })
  prechatNameInput.disabled = true
  prechatEmailInput.disabled = true
  hidePrechat()
}

rateGood.onclick = () => {
  console.log('click')
  sdk.rateChat({ rate: 'good' })
}

rateBad.onclick = () => {
  sdk.rateChat({ rate: 'bad' })
}


fileInput.onchange = (data) => {
  const file = event.target.files[0]
  sdk.sendFile({
    file,
  }).then((res) => console.log('> res', res)).catch((error) => console.log('> error', error))
}
});