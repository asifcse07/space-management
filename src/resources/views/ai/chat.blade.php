@extends('layouts.app')

@section('title', 'AI Space Assistant')
@section('header', 'Space Management AI Assistant')

@section('content')
<div class="chat-container" style="display:flex;flex-direction:column;height:80vh;max-width:700px;margin:30px auto;border-radius:16px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.2);font-family:sans-serif;background:#f7f9fc;">
    
    <!-- Chat Messages -->
    <div id="messages" class="messages" style="flex:1;overflow-y:auto;padding:20px; display:flex; flex-direction:column; gap:10px;"></div>

    <!-- Typing indicator -->
    <div id="typing" style="display:none;padding:10px 20px;font-size:14px;color:#555;">AI is typing...</div>

    <!-- Chat Input -->
    <form id="chat-form" style="display:flex;border-top:1px solid #ddd;background:#fff;">
        <input type="text" id="query" placeholder="Ask about buildings, floors, or areas..." required
               style="flex:1;padding:14px 16px;border:none;outline:none;font-size:16px;">
        <button type="submit"
                style="padding:14px 20px;border:none;background:linear-gradient(90deg,#28a745,#1e7e34);color:white;font-weight:bold;cursor:pointer;transition:0.3s;">
            Send
        </button>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
const form = document.getElementById('chat-form');
const queryInput = document.getElementById('query');
const messagesDiv = document.getElementById('messages');
const typingDiv = document.getElementById('typing');

function appendMessage(text, sender) {
    const msgWrapper = document.createElement('div');
    msgWrapper.style.display = 'flex';
    msgWrapper.style.flexDirection = sender === 'user' ? 'row-reverse' : 'row';
    msgWrapper.style.alignItems = 'flex-end';
    msgWrapper.style.gap = '10px';

    // Avatar
    const avatar = document.createElement('div');
    avatar.style.width = '32px';
    avatar.style.height = '32px';
    avatar.style.borderRadius = '50%';
    avatar.style.background = sender === 'user' ? '#007bff' : '#555';
    avatar.style.display = 'flex';
    avatar.style.justifyContent = 'center';
    avatar.style.alignItems = 'center';
    avatar.style.color = 'white';
    avatar.style.fontWeight = 'bold';
    avatar.innerText = sender === 'user' ? 'U' : 'AI';

    // Message bubble
    const msg = document.createElement('div');
    msg.style.padding = '12px 18px';
    msg.style.borderRadius = '20px';
    msg.style.maxWidth = '70%';
    msg.style.wordWrap = 'break-word';
    msg.style.fontSize = '15px';
    msg.style.lineHeight = '1.4';
    msg.style.background = sender === 'user' ? 'linear-gradient(135deg, #007bff, #0056b3)' : 'linear-gradient(135deg,#f1f1f1,#dcdcdc)';
    msg.style.color = sender === 'user' ? 'white' : '#333';
    msg.style.position = 'relative';
    msg.innerText = text;

    msgWrapper.appendChild(avatar);
    msgWrapper.appendChild(msg);
    messagesDiv.appendChild(msgWrapper);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    let query = queryInput.value.trim();
    if (!query) return;

    appendMessage(query, 'user');
    queryInput.value = '';
    typingDiv.style.display = 'block';

    axios.post('/ai/chat', { query: query })
        .then(res => {
            typingDiv.style.display = 'none';
            appendMessage(res.data.answer, 'ai');
        })
        .catch(err => {
            typingDiv.style.display = 'none';
            appendMessage('Error: ' + err, 'ai');
        });
});
</script>
@endsection
